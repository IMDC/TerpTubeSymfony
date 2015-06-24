<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\MediaConsumer;
use IMDC\TerpTubeBundle\Consumer\Options\MediaConsumerOptions;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AbstractMediaConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AbstractMediaConsumerTest extends MediaTestCase
{
    private static $mediaIds = array(1, 999999);

    public function testInstantiate()
    {
        $consumer = $this->getMediaConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\MediaConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute()
    {
        $opts = new MediaConsumerOptions();
        $consumer = $this->getMediaConsumer();

        // test existing
        $opts->mediaId = self::$mediaIds[0];
        $serialized = $opts->pack();

        $result = $consumer->execute(new AMQPMessage($serialized));
        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);

        // test non-existing
        $opts->mediaId = self::$mediaIds[1];
        $serialized = $opts->pack();

        $result = $consumer->execute(new AMQPMessage($serialized));
        $this->assertEquals(ConsumerInterface::MSG_REJECT, $result);
    }

    /**
     * @return MediaConsumer
     */
    private function getMediaConsumer()
    {
        $logger = $this->container->get('logger');
        $doctrine = $this->container->get('doctrine');
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->container->get('old_sound_rabbit_mq.entity_status_producer');

        return new MediaConsumer($logger, $doctrine, $transcoder, $entityStatusProducer);
    }
}
