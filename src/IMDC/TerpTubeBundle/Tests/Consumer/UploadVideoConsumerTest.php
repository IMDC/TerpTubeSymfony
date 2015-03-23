<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\UploadVideoConsumer;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class UploadVideoConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UploadVideoConsumerTestCase extends MediaTestCase
{
    public function testInstantiate()
    {
        $mediaProducer = $this->getMediaProducer();

        $this->assertNotNull($mediaProducer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\UploadVideoConsumer', $mediaProducer);
    }

    public function testExecute_Video()
    {
        $this->createMedia('video_audio.webm', Media::TYPE_VIDEO);

        $metaData = new MetaData();
        $metaData->setSize(-1);
        $metaData->setTimeUploaded(new \DateTime());
        $this->media->setMetaData($metaData);

        $this->entityManager->persist($this->media);
        $this->entityManager->flush();

        $mediaProducer = $this->getMediaProducer();
        $mediaProducer->execute(new AMQPMessage(serialize(array(
            'media_id' => $this->media->getId()
        ))));

        $this->assertGreaterThan(0, filesize($this->media->getResource()->getAbsolutePath()));
        $this->assertNotNull($metaData->getWidth());
        $this->assertNotNull($metaData->getHeight());
        $this->assertNotNull($metaData->getDuration());
    }

    /**
     * @return UploadVideoConsumer
     */
    private function getMediaProducer()
    {
        $logger = $this->container->get('logger');
        $doctrine = $this->container->get('doctrine');
        $transcoder = $this->container->get('imdc_terptube.transcoder');

        return new UploadVideoConsumer($logger, $doctrine, $transcoder);
    }
}
