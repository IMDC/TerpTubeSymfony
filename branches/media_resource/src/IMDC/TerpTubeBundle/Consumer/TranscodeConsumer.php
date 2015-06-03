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

        if (!array_key_exists('transcodeOpts', $this->message)) {
            $this->logger->error("no transcode options specified");
            return true;
        }

        //TODO change to a class?
        $transcodeOpts = $this->message['transcodeOpts'];

        $mediaType = $this->media->getType();
        //$transcodingType = $this->media->getIsReady();
        $sourceResource = $this->media->getSourceResource();
        $sourceFile = new File($sourceResource->getAbsolutePath());
        $transcodedFile = null;

        try {
            $this->logger->info("Transcoding " . $sourceFile->getRealPath());

            switch ($mediaType) {
                case Media::TYPE_VIDEO:
                    // still check this?
                    //if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO)
                    if ($transcodeOpts['container'] == 'webm')
                        //TODO make the transcoder choose the codec/format based on the container
                        $transcodedFile = $this->transcoder->transcodeToWebM($sourceFile, $transcodeOpts['preset']);

                    // still check this?
                    //if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO)
                    else if ($transcodeOpts['container'] == 'mp4')
                        $transcodedFile = $this->transcoder->transcodeToX264($sourceFile, $transcodeOpts['preset']);

                    //TODO move to UploadListener after consolidation
                    $this->createThumbnail($sourceResource);

                    break;
                case Media::TYPE_AUDIO:
                    // still check this?
                    //if ($transcodingType == Media::READY_MP4 || $transcodingType == Media::READY_NO)
                    if ($transcodeOpts['container'] == 'webm')
                        $transcodedFile = $this->transcoder->transcodeAudioToWebM($sourceFile, $transcodeOpts['preset']);

                    // still check this?
                    //if ($transcodingType == Media::READY_WEBM || $transcodingType == Media::READY_NO)
                    else if ($transcodeOpts['container'] == 'mp4')
                        $transcodedFile = $this->transcoder->transcodeAudioToX264($sourceFile, $transcodeOpts['preset']);

                    break;
            }

            if ($transcodedFile == null)
                throw new \Exception("Could not transcode the video for some reason");

            $this->logger->info("Transcoding complete!");
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            return true;
        }

        // done with the source file. do (not) delete the file itself, but keep the entity
        //unlink($sourceFile->getRealPath());

        $transcodeResource = $this->createResource($transcodedFile);
        $this->media->addResource($transcodeResource);

        //TODO at least one transcode = ready?
        //TODO add state/status check to determine if ready?
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
        //TODO consolidate with call in UploadListener
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
