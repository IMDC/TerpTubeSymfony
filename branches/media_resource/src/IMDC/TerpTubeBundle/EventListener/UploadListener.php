<?php

namespace IMDC\TerpTubeBundle\EventListener;

use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class UploadListener implements EventSubscriberInterface
{

    private $logger;

    private $entityManager;

    private $transcode_producer;

    private $transcoder;

    private $fs;

    public function __construct($logger, $entityManager, $transcode_producer, $transcoder)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->transcode_producer = $transcode_producer;
        $this->transcoder = $transcoder;
        $this->fs = new Filesystem();
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
        $sourceResource = $media->getSourceResource();
        $message = null;
        $fileSize = filesize($sourceResource->getAbsolutePath());

        // Transcode the different types and populate the metadata for the proper type

        $metaData = new MetaData();
        $metaData->setSize(-1);
        $metaData->setTimeUploaded(new \DateTime('now'));

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
                $this->logger->info('Uploaded a video');

                $message = array(
                    'media_id' => $media->getId()
                );

                break;
            case Media::TYPE_AUDIO:
                // TODO look into resizing images
                $this->logger->info('Uploaded an audio');

                $message = array(
                    'media_id' => $media->getId()
                );

                break;
            case Media::TYPE_IMAGE:
                $this->logger->info('Uploaded an image');

                $imageSize = getimagesize($sourceResource->getAbsolutePath());

                $metaData->setSize($fileSize);
                $metaData->setWidth($imageSize[0]);
                $metaData->setHeight($imageSize[1]);

                $media->setIsReady(Media::READY_YES);

                // duplicate of this in UploadVideoConsumer
                try {
                    $thumbnailTempFile = $this->transcoder->createThumbnail($sourceResource->getAbsolutePath(), Media::TYPE_IMAGE);
                    $thumbnailFile = $media->getThumbnailRootDir() . "/" . $sourceResource->getId() . ".png";
                    $this->fs->rename($thumbnailTempFile, $thumbnailFile, true);
                    $media->setThumbnailPath($sourceResource->getId() . ".png");
                } catch (IOException $e) {
                    $this->logger->error($e->getTraceAsString());
                }

                break;
            default:
                $this->logger->info('Uploaded something');

                $metaData->setSize($fileSize);

                $media->setIsReady(Media::READY_YES);
        }

        $this->entityManager->persist($metaData);

        $media->setMetaData($metaData);

        $this->entityManager->flush();

        switch ($media->getType()) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                $this->transcode_producer->publish(serialize($message));
                break;
        }
    }
}
