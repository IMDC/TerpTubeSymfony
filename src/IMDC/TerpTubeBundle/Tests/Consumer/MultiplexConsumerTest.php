<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\MultiplexConsumer;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexOperation;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class MultiplexConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MultiplexConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $consumer = $this->getMultiplexConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\MultiplexConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute_Mux()
    {
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_recorded_tomux_1_1');
        $paths = explode('|', $media->getTitle());

        $opts = new MultiplexConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->operation = MultiplexOperation::MUX;
        $opts->videoPath = $paths[1];
        $opts->audioPath = $paths[2];
        $serialized = $opts->pack();

        $consumer = $this->getMultiplexConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
    }

    public function testExecute_Remux()
    {
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_recorded_toremux_1_1');
        $paths = explode('|', $media->getTitle());

        $opts = new MultiplexConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->operation = MultiplexOperation::REMUX;
        $opts->audioPath = $paths[2];
        $serialized = $opts->pack();

        $consumer = $this->getMultiplexConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
    }

    /**
     * @return MultiplexConsumer
     */
    private function getMultiplexConsumer()
    {
        $logger = $this->getContainer()->get('logger');
        $doctrine = $this->getContainer()->get('doctrine');
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->getContainer()->get('old_sound_rabbit_mq.entity_status_producer');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');
        $transcodeProducer = $this->getContainer()->get('old_sound_rabbit_mq.transcode_producer');

        return new MultiplexConsumer($logger, $doctrine, $transcoder,
            $entityStatusProducer, $resourceFileConfig, $transcodeProducer);
    }
}
