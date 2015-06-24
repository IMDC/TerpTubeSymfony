<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Consumer\Options\TranscodeConsumerOptions;
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

        if (!($this->message instanceof TranscodeConsumerOptions)) {
            $this->logger->error("no transcode options specified");
            return self::MSG_REJECT;
        }
        /** @var TranscodeConsumerOptions $transcodeOpts */
        $transcodeOpts = TranscodeConsumerOptions::unpack($msg->body);

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media->setState(MediaStateConst::PROCESSING);
        $em->persist($this->media);
        $em->flush();

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
            return self::MSG_REJECT;
        }

        // done with the source file. do (not) delete the file itself, but keep the entity
        //unlink($sourceFile->getRealPath());

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->refresh($this->media);

        $transcodeResource = $this->createResource($transcodedFile);
        $this->media->addResource($transcodeResource);

        if ($this->isReady())
            $this->media->setState(MediaStateConst::READY);

        $em->persist($this->media);
        $em->flush();

        return self::MSG_ACK;
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
