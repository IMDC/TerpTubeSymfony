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
            $sourceResource = $this->media->getSourceResource();
            $sourceFile = new File($sourceResource->getAbsolutePath());

            $transcodingType = $this->media->getIsReady();

            if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $sourceFile->getRealPath());
                $webmFile = $this->transcoder->transcodeAudioToWebM($sourceFile, 'ffmpeg.webm_audio');
            }

            if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO) {
                $this->logger->info("Transcoding " . $sourceFile->getRealPath());
                $mp4File = $this->transcoder->transcodeAudioToX264($sourceFile, 'ffmpeg.aac_audio');
            }

            if ($webmFile == null && $mp4File == null) {
                $this->logger->error("Could not transcode the audio for some reason");
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
                $this->logger->info("Resource m4a does not exist");

                $mp4Resource = $this->createResource($mp4File);
                $this->media->addResource($mp4Resource);
            }

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
}
