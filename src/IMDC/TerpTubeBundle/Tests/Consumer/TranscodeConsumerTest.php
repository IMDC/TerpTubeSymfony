<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\Options\TranscodeConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\TranscodeConsumer;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Transcoding\ContainerConst;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TranscodeConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TranscodeConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $consumer = $this->getTranscodeConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\TranscodeConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute()
    {
        // after multiplexing, a recorded media should be equivalent to an uploaded one
        //$media = $this->createUploadedMedia('video_audio.webm', Media::TYPE_VIDEO);
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_media_1_1');

        $opts = new TranscodeConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->container = ContainerConst::MP4;
        $opts->preset = 'ffmpeg.x264_720p_video';
        $serialized = $opts->pack();

        $consumer = $this->getTranscodeConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        //$this->entityManager->refresh($media);

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
        //$this->assertEquals(1, $media->getResources()->count());
    }

    /**
     * @return TranscodeConsumer
     */
    private function getTranscodeConsumer()
    {
        $logger = $this->getContainer()->get('logger');
        $doctrine = $this->getContainer()->get('doctrine');
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->getContainer()->get('old_sound_rabbit_mq.entity_status_producer');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');

        return new TranscodeConsumer($logger, $doctrine, $transcoder, $entityStatusProducer, $resourceFileConfig);
    }
}
