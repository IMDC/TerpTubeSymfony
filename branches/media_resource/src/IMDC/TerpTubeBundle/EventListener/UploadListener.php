<?php

namespace IMDC\TerpTubeBundle\EventListener;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Transcoding\TranscodeContainer;
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

                $messages[] = array(
                    'media_id' => $media->getId(),
                    'transcodeOpts' => array(
                        'container' => TranscodeContainer::WEBM,
                        'preset' => 'ffmpeg.webm_720p_video'
                    ));
                $messages[] = array(
                    'media_id' => $media->getId(),
                    'transcodeOpts' => array(
                        'container' => TranscodeContainer::MP4,
                        'preset' => 'ffmpeg.x264_720p_video'
                    ));

                $media->createThumbnail($this->transcoder);

                break;
            case Media::TYPE_AUDIO:
                // TODO look into resizing images
                $this->logger->info('Uploaded an audio');

                $messages[] = array(
                    'media_id' => $media->getId(),
                    'transcodeOpts' => array(
                        'container' => TranscodeContainer::WEBM,
                        'preset' => 'ffmpeg.webm_audio'
                    ));
                $messages[] = array(
                    'media_id' => $media->getId(),
                    'transcodeOpts' => array(
                        'container' => TranscodeContainer::MP4,
                        'preset' => 'ffmpeg.aac_audio'
                    ));

                break;
            case Media::TYPE_IMAGE:
                $this->logger->info('Uploaded an image');

                $sourceResourcePath = $media->getSourceResource()->getAbsolutePath();
                $imageSize = getimagesize($sourceResourcePath);

                $metaData->setSize(filesize($sourceResourcePath));
                $metaData->setWidth($imageSize[0]);
                $metaData->setHeight($imageSize[1]);

                $media->createThumbnail($this->transcoder);

                $media->setIsReady(Media::READY_YES);

                break;
            default:
                $this->logger->info('Uploaded something');

                $metaData->setSize(filesize($media->getSourceResource()->getAbsolutePath()));

                $media->setIsReady(Media::READY_YES);
        }

        $this->entityManager->persist($metaData);

        $media->setMetaData($metaData);

        $this->entityManager->flush();

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                foreach ($messages as $message)
                    $this->transcodeProducer->publish(serialize($message));

                break;
        }
    }
}
