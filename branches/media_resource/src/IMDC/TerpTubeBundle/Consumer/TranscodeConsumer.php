<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\File\File;

class TranscodeConsumer extends AbstractMediaConsumer
{
    public function execute(AMQPMessage $msg)
    {
        // extracts media from message
        // $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $result = parent::execute($msg);

        if (empty($this->media))
            return $result;

        //TODO change to a class?
        /*$transcodeOpts = array_key_exists('transcodeOpts', $this->message) ? $this->message['transcodeOpts'] : null;
        if (empty($transcodeOpts)) {
            $this->logger->error("no transcode options specified");
            return true;
        }*/

        if (!($this->message instanceof TranscodeConsumerOptions)) {
            $this->logger->error("no transcode options specified");
            return true;
        }
        /** @var TranscodeConsumerOptions $transcodeOpts */
        $transcodeOpts = TranscodeConsumerOptions::unpack($msg->body);

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media->setIsReady(MediaStateConst::PROCESSING);
        $em->persist($this->media);
        $em->flush();
        //$em->close();

        //TODO move to separate consumer?
        if (($transcodeOpts->mux || $transcodeOpts->remux)
            && $this->media->getSourceResource()->getPath() === 'placeholder' // if not already done
        ) {
            try {
                $resourceFile = null;

                if ($transcodeOpts->mux)
                    $resourceFile = $this->transcoder->mergeAudioVideo($transcodeOpts->audio, $transcodeOpts->video);

                if ($transcodeOpts->remux)
                    $resourceFile = $this->transcoder->remuxWebM($transcodeOpts->audio);

                if ($resourceFile != null) {
                    $em = $this->doctrine->getManager();

                    $resourceFile = $this->media->getSourceResource()
                        ->setFile(IMDCFile::fromFile($resourceFile))
                        ->setPath($resourceFile->getExtension());
                    $em->persist($resourceFile);
                    $em->flush();

                    $this->updateMetaData($resourceFile);
                    $this->media->createThumbnail($this->transcoder);
                    $em->persist($this->media);
                    $em->flush();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getTraceAsString());
                return true;
            }
        }

        $sourceFile = new File($this->media->getSourceResource()->getAbsolutePath());
        $transcodedFile = null;

        try {
            $this->logger->info("Transcoding " . $sourceFile->getRealPath());

            $transcodedFile = $this->transcoder->transcode(
                $transcodeOpts->container, $this->media->getType(), $sourceFile, $transcodeOpts->preset);
            if ($transcodedFile == null)
                throw new \Exception("Could not transcode the video for some reason");

            $this->logger->info("Transcoding complete!");
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            return true;
        }

        // done with the source file. do (not) delete the file itself, but keep the entity
        //unlink($sourceFile->getRealPath());

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->refresh($this->media);

        $transcodeResource = $this->createResource($transcodedFile);
        $this->media->addResource($transcodeResource);

        if ($this->isReady())
            $this->media->setIsReady(MediaStateConst::READY);

        $em->persist($this->media);
        $em->flush();
        //$em->close();

        /*if ($this->checkForPendingOperations($this->media->getId())) {
            $this->executePendingOperations($this->media->getId());
        }*/

        return true;
    }

    /**
     * check for webm and mp4 containers
     * @return bool
     */
    private function isReady()
    {
        $count = 0;

        /** @var ResourceFile $resource */
        foreach ($this->media->getResources() as $resource) {
            if ($resource->getPath() === 'webm' || $resource->getPath() === 'mp4')
                $count++;
        }

        return ($count >= 2);
    }
}
