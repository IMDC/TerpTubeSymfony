<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\Options\TrimConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\TrimConsumer;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TrimConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TrimConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $consumer = $this->getTrimConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\TrimConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute()
    {
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_transcoded_1_1');

        $opts = new TrimConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->startTime = 0.1;
        $opts->endTime = 3.5;
        //$opts->currentDuration = $resource->getMetaData()->getDuration();
        $serialized = $opts->pack();

        $consumer = $this->getTrimConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
        //$this->assertLessThan($opts->currentDuration, $resource->getMetaData()->getDuration());
    }

    /**
     * @return TrimConsumer
     */
    private function getTrimConsumer()
    {
        $logger = $this->getContainer()->get('logger');
        $doctrine = $this->getContainer()->get('doctrine');
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->getContainer()->get('old_sound_rabbit_mq.entity_status_producer');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');

        return new TrimConsumer($logger, $doctrine, $transcoder, $entityStatusProducer, $resourceFileConfig);
    }
}
