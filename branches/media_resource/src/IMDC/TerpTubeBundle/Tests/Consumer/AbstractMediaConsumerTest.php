<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\MediaConsumer;
use IMDC\TerpTubeBundle\Consumer\Options\MediaConsumerOptions;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AbstractMediaConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AbstractMediaConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $consumer = $this->getMediaConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\MediaConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute()
    {
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_media_1_1');
        $opts = new MediaConsumerOptions();
        $consumer = $this->getMediaConsumer();

        // test existing
        $opts->mediaId = $media->getId();
        $serialized = $opts->pack();

        $result = $consumer->execute(new AMQPMessage($serialized));
        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);

        // test non-existing
        $opts->mediaId = 999999;
        $serialized = $opts->pack();

        $result = $consumer->execute(new AMQPMessage($serialized));
        $this->assertEquals(ConsumerInterface::MSG_REJECT, $result);
    }

    /**
     * @return MediaConsumer
     */
    private function getMediaConsumer()
    {
        $logger = $this->getContainer()->get('logger');
        $doctrine = $this->getContainer()->get('doctrine');
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->getContainer()->get('old_sound_rabbit_mq.entity_status_producer');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');

        return new MediaConsumer($logger, $doctrine, $transcoder, $entityStatusProducer, $resourceFileConfig);
    }
}
