<?php

namespace IMDC\TerpTubeBundle\Consumer;

use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Entity\Media;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Exception\IOException;

class TranscodeConsumer extends MediaBaseConsumer
{
    public function execute(AMQPMessage $msg)
    {
        // extracts media from message
        // $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $result = parent::execute($msg);

        if (empty($this->media))
            return $result;

        $mediaType = $this->media->getType();
        $transcodingType = $this->media->getIsReady();
        $sourceResource = $this->media->getSourceResource();
        $sourceFile = new File($sourceResource->getAbsolutePath());

        try {
            $this->logger->info("Transcoding " . $sourceFile->getRealPath());

            switch ($mediaType) {
                case Media::TYPE_VIDEO:
                    if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO)
                        $webmFile = $this->transcoder->transcodeToWebM($sourceFile, 'ffmpeg.webm_720p_video');

                    if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO)
                        $mp4File = $this->transcoder->transcodeToX264($sourceFile, 'ffmpeg.x264_720p_video');

                    break;
                case Media::TYPE_AUDIO:
                    if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO)
                        $webmFile = $this->transcoder->transcodeAudioToWebM($sourceFile, 'ffmpeg.webm_audio');

                    if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO)
                        $mp4File = $this->transcoder->transcodeAudioToX264($sourceFile, 'ffmpeg.aac_audio');

                    break;
            }

            if ($webmFile == null && $mp4File == null)
                throw new \Exception("Could not transcode the video for some reason");

            $this->logger->info("Transcoding complete!");
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            return true;
        }

        // done with the source file. do (not) delete the file itself, but keep the entity
        //unlink($sourceFile->getRealPath());

        if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO) {
            $webmResource = $this->createResource($webmFile);
            $this->media->addResource($webmResource);
        }

        if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO) {
            $mp4Resource = $this->createResource($mp4File);
            $this->media->addResource($mp4Resource);
        }

        if ($mediaType == Media::TYPE_VIDEO)
            $this->createThumbnail($sourceResource);

        $this->media->setIsReady(Media::READY_YES);

        $this->entityManager->persist($this->media);
        $this->entityManager->flush();

        if ($this->checkForPendingOperations($this->media->getId())) {
            $this->executePendingOperations($this->media->getId());
        }

        return true;
    }

    private function createThumbnail($sourceResource)
    {
        try {
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
