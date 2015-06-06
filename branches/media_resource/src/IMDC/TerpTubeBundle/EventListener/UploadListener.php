<?php

namespace IMDC\TerpTubeBundle\EventListener;

use Doctrine\ORM\EntityManager;
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

    public function __construct($logger, $entityManager, $transcoder, $transcodeProducer)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->transcoder = $transcoder;
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
        $messages = array();

        // Transcode the different types and populate the metadata for the proper type

        $metaData = new MetaData();
        $metaData->setSize(-1);
        $metaData->setTimeUploaded(new \DateTime('now'));

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
                $this->logger->info('Uploaded a video');

                $mux = is_file($event->getTmpVideoPath()) && is_file($event->getTmpAudioPath());
                $remux = !is_file($event->getTmpVideoPath()) && is_file($event->getTmpAudioPath());

                $opts = new TranscodeConsumerOptions();
                $opts->mediaId = $media->getId();
                $opts->container = ContainerConst::WEBM;
                $opts->preset = 'ffmpeg.webm_720p_video';
                $opts->mux = $mux;
                $opts->remux = $remux;
                $opts->videoPath = $event->getTmpVideoPath();
                $opts->audioPath = $event->getTmpAudioPath();
                $messages[] = $opts->pack();

                $opts->container = ContainerConst::MP4;
                $opts->preset = 'ffmpeg.x264_720p_video';
                $messages[] = $opts->pack();

                if (!$mux && !$remux)
                    // make it later in TranscodeConsumer
                    $media->createThumbnail($this->transcoder);

                break;
            case Media::TYPE_AUDIO:
                $this->logger->info('Uploaded an audio');

                $opts = new TranscodeConsumerOptions();
                $opts->mediaId = $media->getId();
                $opts->container = ContainerConst::WEBM;
                $opts->preset = 'ffmpeg.webm_audio';
                $messages[] = $opts->pack();

                $opts->container = ContainerConst::MP4;
                $opts->preset = 'ffmpeg.aac_audio';
                $messages[] = $opts->pack();

                break;
            case Media::TYPE_IMAGE:
                // TODO look into resizing images
                $this->logger->info('Uploaded an image');

                $sourceResourcePath = $media->getSourceResource()->getAbsolutePath();
                $imageSize = getimagesize($sourceResourcePath);

                $metaData->setSize(filesize($sourceResourcePath));
                $metaData->setWidth($imageSize[0]);
                $metaData->setHeight($imageSize[1]);

                $media->createThumbnail($this->transcoder);

                $media->setIsReady(MediaStateConst::READY);

                break;
            default:
                $this->logger->info('Uploaded something');

                $metaData->setSize(filesize($media->getSourceResource()->getAbsolutePath()));

                $media->setIsReady(MediaStateConst::READY);
        }

        $this->entityManager->persist($metaData);

        $media->setMetaData($metaData);

        $this->entityManager->flush();

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                foreach ($messages as $message)
                    $this->transcodeProducer->publish($message);

                break;
        }
    }
}
