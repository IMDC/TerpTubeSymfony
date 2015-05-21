<?php

namespace IMDC\TerpTubeBundle\Consumer;

use FFMpeg\FFProbe;
use IMDC\TerpTubeBundle\Entity\Media;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\File\File;

class UploadAudioConsumer extends MediaBaseConsumer
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
            $resourceFile = new File($resource->getAbsolutePath());

            $transcodingType = $this->media->getIsReady();

            if ($transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeAudioToX264($resourceFile, 'ffmpeg.aac_audio');
                $webmFile = $this->transcoder->transcodeAudioToWebM($resourceFile, 'ffmpeg.webm_audio');
            } else if ($transcodingType == Media::READY_MP4) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $webmFile = $this->transcoder->transcodeAudioToWebM($resourceFile, 'ffmpeg.webm_audio');
                $mp4File = $resourceFile;
            } else if ($transcodingType == Media::READY_WEBM) {
                $this->logger->info("Transcoding " . $resourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeAudioToX264($resourceFile, 'ffmpeg.aac_audio');
                $webmFile = $resourceFile;
            } else {
                // Already Transcoded should not be here
                $this->logger->error("Should not be in this place of transcoding when everything is already completed!");
                $webmFile = $resourceFile;
                $mp4File = $resourceFile;
            }

            // Correct the permissions to 664
            $old = umask(0);
            chmod($mp4File->getRealPath(), 0664);
            chmod($webmFile->getRealPath(), 0664);
            umask($old);

            if ($resourceFile->getRealPath() != $mp4File->getRealPath() && $resourceFile->getRealPath() != $webmFile->getRealPath())
                unlink($resourceFile->getRealPath());

            if ($resourceFile->getRealPath() != $webmFile->getRealPath())
                $this->fs->rename($webmFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
            if ($resourceFile->getRealPath() != $mp4File->getRealPath())
                $this->fs->rename($mp4File, $resource->getUploadRootDir() . '/' . $resource->getId() . '.m4a');

            $resource->setPath('m4a');
            $resource->setUpdated(new \DateTime('now'));
            $this->media->setIsReady(Media::READY_YES);

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
}
