<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\Options\TrimConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\TrimConsumer;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TrimConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TrimConsumerTest extends MediaTestCase
{
    public function testInstantiate()
    {
        $consumer = $this->getTrimConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\TrimConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute()
    {
        $media = $this->createTranscodedMedia('video_audio.webm');

        //** @var ResourceFile $resource */
        //$resource = $media->getResources()->get(0);

        $opts = new TrimConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->startTime = 0.1;
        $opts->endTime = 3.5;
        //$opts->currentDuration = $resource->getMetaData()->getDuration();
        $serialized = $opts->pack();

        $consumer = $this->getTrimConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->entityManager->refresh($media);

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
        //$this->assertLessThan($opts->currentDuration, $resource->getMetaData()->getDuration());

        $this->deleteTranscodedMedia($media);
    }

    /**
     * @return TrimConsumer
     */
    private function getTrimConsumer()
    {
        $logger = $this->container->get('logger');
        $doctrine = $this->container->get('doctrine');
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->container->get('old_sound_rabbit_mq.entity_status_producer');

        return new TrimConsumer($logger, $doctrine, $transcoder, $entityStatusProducer);
    }
}
