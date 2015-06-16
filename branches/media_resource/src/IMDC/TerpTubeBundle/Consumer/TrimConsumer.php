<?php

namespace IMDC\TerpTubeBundle\Consumer;

use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\File\File;

class TrimConsumer extends AbstractMediaConsumer
{
    public function execute(AMQPMessage $msg)
    {
        $result = parent::execute($msg);

        if (empty($this->media))
            return $result;

        if ($this->media->getState() !== MediaStateConst::READY) {
            $this->logger->error("media must be in a ready state");
            return self::MSG_REJECT_REQUEUE;
        }

        if (!($this->message instanceof TrimConsumerOptions)) {
            $this->logger->error("no trim options specified");
            return self::MSG_REJECT;
        }
        /** @var TrimConsumerOptions $trimOpts */
        $trimOpts = TrimConsumerOptions::unpack($msg->body);

        //TODO check me. may not work as expected
        if (!$this->isValid($trimOpts->currentDuration)) {
            $this->logger->error("one or more resources have changed since this trim was requested. discarding");
            return self::MSG_REJECT;
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media->setState(MediaStateConst::PROCESSING);
        $em->persist($this->media);
        $em->flush();

        /** @var ResourceFile $resource */
        foreach ($this->media->getResources() as $resource) {
            $sourceFile = new File($resource->getAbsolutePath());

            try {
                if (!$this->transcoder->trimVideo($sourceFile, $trimOpts->startTime, $trimOpts->endTime))
                    throw new \Exception("trim failed");
            } catch (\Exception $e) {
                $this->logger->error($e->getTraceAsString());
                return self::MSG_REJECT;
            }

            // source file is now the trimmed file. refresh
            //$sourceFile = new File($resource->getAbsolutePath());

            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();
            $em->refresh($resource);
            $resource->updateMetaData($this->media->getType(), $this->transcoder);
            $em->persist($resource);
            $em->flush();
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->refresh($this->media);
        $this->media->setState(MediaStateConst::READY);
        $em->persist($this->media);
        $em->flush();

        return self::MSG_ACK;
    }

    /**
     * check if resource at index 0 has been trimmed since the current trim was requested
     * @param $duration
     * @return bool
     */
    private function isValid($duration)
    {
        /** @var ResourceFile $resource */
        $resource = $this->media->getResources()->get(0);

        return ($duration == $resource->getMetaData()->getDuration());
    }
}
