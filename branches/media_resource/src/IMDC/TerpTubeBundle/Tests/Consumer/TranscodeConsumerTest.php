<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\Options\TranscodeConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\TranscodeConsumer;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use IMDC\TerpTubeBundle\Transcoding\ContainerConst;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TranscodeConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TranscodeConsumerTest extends MediaTestCase
{
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
        $media = $this->createUploadedMedia('video_audio.webm', Media::TYPE_VIDEO);

        $opts = new TranscodeConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->container = ContainerConst::MP4;
        $opts->preset = 'ffmpeg.x264_720p_video';
        $serialized = $opts->pack();

        $consumer = $this->getTranscodeConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->entityManager->refresh($media);

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
        $this->assertEquals(1, $media->getResources()->count());

        $this->deleteUploadedMedia($media);
    }

    /**
     * @return TranscodeConsumer
     */
    private function getTranscodeConsumer()
    {
        $logger = $this->container->get('logger');
        $doctrine = $this->container->get('doctrine');
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->container->get('old_sound_rabbit_mq.entity_status_producer');

        return new TranscodeConsumer($logger, $doctrine, $transcoder, $entityStatusProducer);
    }
}
