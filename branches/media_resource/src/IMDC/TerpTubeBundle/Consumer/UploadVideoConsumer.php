<?php

namespace IMDC\TerpTubeBundle\Consumer;

use FFMpeg\FFProbe;
use IMDC\TerpTubeBundle\Entity\Media;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;

class UploadVideoConsumer extends MediaBaseConsumer
{
    public function execute(AMQPMessage $msg)
    {
        // extracts media from message
        $result = parent::execute($msg);

        if (empty($this->media))
            return $result;

        // Process video upload.
        // $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        try {
            $sourceResource = $this->media->getSourceResource();
            $sourceFile = new File($sourceResource->getAbsolutePath());

            $transcodingType = $this->media->getIsReady();

            if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $sourceFile->getRealPath());
                $webmFile = $this->transcoder->transcodeToWebM($sourceFile, 'ffmpeg.webm_720p_video');
            }

            if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $sourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeToX264($sourceFile, 'ffmpeg.x264_720p_video');
            }

            if ($webmFile == null && $mp4File == null) {
                $this->logger->error("Could not transcode the video for some reason");
                return false;
            }

            // done with the source file. do (not) delete the file itself, but keep the entity
            //unlink($sourceFile->getRealPath());

            if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO) {
                $this->logger->info("Resource webm does not exist");

                $webmResource = $this->createResource($webmFile);
                $this->media->addResource($webmResource);
            }

            if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO) {
                $this->logger->info("Resource mp4 does not exist");

                $mp4Resource = $this->createResource($mp4File);
                $this->media->addResource($mp4Resource);
            }

            $this->createThumbnail();

            $this->media->setIsReady(Media::READY_YES);

            $this->entityManager->persist($this->media);
            $this->entityManager->flush();

            if ($this->checkForPendingOperations($this->media->getId())) {
                $this->executePendingOperations($this->media->getId());
            }

            $this->logger->info("Transcoding complete!");
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            return false;
        }

        return true;
    }

    private function createThumbnail()
    {
        try {
            $sourceResource = $this->media->getSourceResource();

            // Get a thumbnail
            $thumbnailTempFile = $this->transcoder->createThumbnail($sourceResource->getAbsolutePath(), $this->media->getType());
            $thumbnailFile = $this->media->getThumbnailRootDir() . "/" . $sourceResource->getId() . ".png";
            $this->fs->rename($thumbnailTempFile, $thumbnailFile, true);
            $this->media->setThumbnailPath($sourceResource->getId() . ".png");
        } catch (IOException $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }
}
