<?php

namespace IMDC\TerpTubeBundle\EventListener;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Consumer\MultiplexConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\MultiplexOperation;
use IMDC\TerpTubeBundle\Consumer\TranscodeConsumerOptions;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Transcoding\ContainerConst;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UploadListener implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Transcoder
     */
    private $transcoder;

    /**
     * @var Producer
     */
    private $transcodeProducer;

    public function __construct($logger, $entityManager, $transcoder, $multiplexProducer, $transcodeProducer)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->transcoder = $transcoder;
        $this->multiplexProducer = $multiplexProducer;
        $this->transcodeProducer = $transcodeProducer;
    }

    public static function getSubscribedEvents()
    {
        return array(
            UploadEvent::EVENT_UPLOAD => 'onUpload'
        );
    }

    /**
     * Trigerred when a file is uploaded
     *
     * @param UploadEvent $event
     */
    public function onUpload(UploadEvent $event)
    {
        $media = $event->getMedia();
        $media->getSourceResource()->setMetaData(new MetaData());

        // Transcode the different types and populate the metadata for the proper type

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
                $this->logger->info('Uploaded a video');

                $mux = is_file($event->getTmpVideoPath()) && is_file($event->getTmpAudioPath());
                $remux = !is_file($event->getTmpVideoPath()) && is_file($event->getTmpAudioPath());

                if ($mux || $remux) {
                    $opts = new MultiplexConsumerOptions();
                    $opts->mediaId = $media->getId();
                    $opts->operation = $mux ? MultiplexOperation::MUX : MultiplexOperation::REMUX;
                    $opts->videoPath = $event->getTmpVideoPath();
                    $opts->audioPath = $event->getTmpAudioPath();

                    $this->multiplexProducer->publish($opts->pack());

                    // make thumbnail and queue for transcode later in MultiplexConsumer

                    break;
                }

                $opts = new TranscodeConsumerOptions();
                $opts->mediaId = $media->getId();
                $opts->container = ContainerConst::WEBM;
                $opts->preset = 'ffmpeg.webm_720p_video';
                $this->transcodeProducer->publish($opts->pack());

                $opts->container = ContainerConst::MP4;
                $opts->preset = 'ffmpeg.x264_720p_video';
                $this->transcodeProducer->publish($opts->pack());

                $media->createThumbnail($this->transcoder);

                break;
            case Media::TYPE_AUDIO:
                $this->logger->info('Uploaded an audio');

                $opts = new TranscodeConsumerOptions();
                $opts->mediaId = $media->getId();
                $opts->container = ContainerConst::WEBM;
                $opts->preset = 'ffmpeg.webm_audio';
                $this->transcodeProducer->publish($opts->pack());

                $opts->container = ContainerConst::MP4;
                $opts->preset = 'ffmpeg.aac_audio';
                $this->transcodeProducer->publish($opts->pack());

                break;
            case Media::TYPE_IMAGE:
                // TODO look into resizing images
                $this->logger->info('Uploaded an image');

                $media->createThumbnail($this->transcoder);

            // no break
            default:
                if ($media->getType() != Media::TYPE_IMAGE)
                    $this->logger->info('Uploaded something');

                $media->getSourceResource()->updateMetaData(
                    $media->getType(),
                    $this->transcoder);

                $media->setState(MediaStateConst::READY);
        }

        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }
}
