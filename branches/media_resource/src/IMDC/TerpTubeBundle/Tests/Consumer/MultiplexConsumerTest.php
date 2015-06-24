<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\MultiplexConsumer;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexOperation;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MultiplexConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MultiplexConsumerTest extends MediaTestCase
{
    public function testInstantiate()
    {
        $consumer = $this->getMultiplexConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\MultiplexConsumer', $consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer', $consumer);
    }

    public function testExecute_Mux()
    {
        $recording = $this->createRecordedMedia('video.webm', 'audio.wav');

        /** @var UploadedFile $video */
        $video = $recording[0];
        /** @var UploadedFile $audio */
        $audio = $recording[1];
        /** @var Media $media */
        $media = $recording[2];

        $opts = new MultiplexConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->operation = MultiplexOperation::MUX;
        $opts->videoPath = $video->getPathname();
        $opts->audioPath = $audio->getPathname();
        $serialized = $opts->pack();

        $consumer = $this->getMultiplexConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);

        $this->deleteRecordedMedia($recording);
    }

    public function testExecute_Remux()
    {
        $recording = $this->createRecordedMedia(null, 'video_audio.webm', true);

        /** @var UploadedFile $audio */
        $audio = $recording[1];
        /** @var Media $media */
        $media = $recording[2];

        $opts = new MultiplexConsumerOptions();
        $opts->mediaId = $media->getId();
        $opts->operation = MultiplexOperation::REMUX;
        $opts->audioPath = $audio->getPathname();
        $serialized = $opts->pack();

        $consumer = $this->getMultiplexConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);

        $this->deleteRecordedMedia($recording);
    }

    /**
     * @return MultiplexConsumer
     */
    private function getMultiplexConsumer()
    {
        $logger = $this->container->get('logger');
        $doctrine = $this->container->get('doctrine');
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $entityStatusProducer = $this->container->get('old_sound_rabbit_mq.entity_status_producer');
        $transcodeProducer = $this->container->get('old_sound_rabbit_mq.transcode_producer');

        return new MultiplexConsumer($logger, $doctrine, $transcoder, $entityStatusProducer, $transcodeProducer);
    }
}
