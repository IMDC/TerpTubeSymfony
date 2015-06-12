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
            return true;
        }

        if (!($this->message instanceof TrimConsumerOptions)) {
            $this->logger->error("no trim options specified");
            return true;
        }
        /** @var TrimConsumerOptions $trimOpts */
        $trimOpts = TrimConsumerOptions::unpack($msg->body);

        if (!$this->isValid(intval($trimOpts->timestamp))) {
            $this->logger->error("one or more resources have changed since this trim was requested. discarding");
            return true;
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media->setState(MediaStateConst::PROCESSING);
        $em->persist($this->media);
        $em->flush();
        //$em->close();

        /** @var ResourceFile $resource */
        foreach ($this->media->getResources() as $resource) {
            $sourceFile = new File($resource->getAbsolutePath());

            try {
                if (!$this->transcoder->trimVideo($sourceFile, $trimOpts->startTime, $trimOpts->endTime))
                    throw new \Exception("trim failed");
            } catch (\Exception $e) {
                $this->logger->error($e->getTraceAsString());
                return true;
            }

            // source file is now the trimmed file. refresh
            //$sourceFile = new File($resource->getAbsolutePath());

            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();
            $em->refresh($resource);
            $this->updateMetaData($resource);
            $em->persist($resource);
            $em->flush();
            //$em->close();
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->refresh($this->media);
        $this->media->setState(MediaStateConst::READY);
        $em->persist($this->media);
        $em->flush();
        //$em->close();

        return true;
    }

    /**
     * check if resources have changed since the trim was requested
     * @return bool
     */
    private function isValid($timestamp)
    {
        $containerCount = 0;
        $count = 0;

        /** @var ResourceFile $resource */
        foreach ($this->media->getResources() as $resource) {
            if ($resource->getPath() === 'webm' || $resource->getPath() === 'mp4') {
                $containerCount++;

                if ($timestamp >= $resource->getUpdated()->getTimestamp())
                    $count++;
            }
        }

        return ($containerCount == $count);
    }
}
