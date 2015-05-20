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
            $resource = $this->media->getResource();
            $resourceFile = new File ($resource->getAbsolutePath());

            // Grab the width/height first to convert to the nearest standard resolution.
            $transcodingType = $this->media->getIsReady();
            if ($transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeToX264($resourceFile, 'ffmpeg.x264_720p_video');
                $webmFile = $this->transcoder->transcodeToWebM($resourceFile, 'ffmpeg.webm_720p_video');
            } else if ($transcodingType == Media::READY_MP4) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $webmFile = $this->transcoder->transcodeToWebM($resourceFile, 'ffmpeg.webm_720p_video');
                $mp4File = $resourceFile;
            } else if ($transcodingType == Media::READY_WEBM) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeToX264($resourceFile, 'ffmpeg.x264_720p_video');
                $webmFile = $resourceFile;
            } else {
                // Already Transcoded should not be here
                $this->logger->error("Should not be in this place of transcoding when everything is already completed!");
                return true;
            }
            // Create a thumbnail
            if ($mp4File == null || $webmFile == null) {
                $this->logger->error("Could not transcode the video for some reason");
                return false;
            }

            // Correct the permissions to 664
            $old = umask(0);
            chmod($mp4File->getRealPath(), 0664);
            chmod($webmFile->getRealPath(), 0664);
            umask($old);

            if ($resourceFile->getRealPath() != $mp4File->getRealPath() && $resourceFile->getRealPath() != $webmFile->getRealPath())
                unlink($resourceFile->getRealPath());

            if ($transcodingType == Media::READY_NO) {
                $this->logger->info("Resource webm does not exist");
                $this->fs->rename($webmFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm', true);
                $this->logger->info("Resource mp4 does not exist");
                $this->fs->rename($mp4File, $resource->getUploadRootDir() . '/' . $resource->getId() . '.mp4', true);
            } else if ($transcodingType == Media::READY_MP4) {
                $this->logger->info("Resource webm does not exist");
                $this->fs->rename($webmFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm', true);
            } else if ($transcodingType == Media::READY_WEBM) {
                $this->logger->info("Resource mp4 does not exist");
                $this->fs->rename($mp4File, $resource->getUploadRootDir() . '/' . $resource->getId() . '.mp4', true);
            }

            $resource->setPath('mp4');
            $this->media->setIsReady(Media::READY_YES);

            $this->createThumbnail();
            $this->updateMetaData();

            $em = $this->doctrine->getManager();
            $em->persist($this->media);
            $em->flush();

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
            // Get a thumbnail
            $thumbnailTempFile = $this->transcoder->createThumbnail($this->media->getResource()->getAbsolutePath(), $this->media->getType());
            $thumbnailFile = $this->media->getThumbnailRootDir() . "/" . $this->media->getResource()->getId() . ".png";
            $this->fs->rename($thumbnailTempFile, $thumbnailFile, true);
            $this->media->setThumbnailPath($this->media->getResource()->getId() . ".png");
        } catch (IOException $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }
}
