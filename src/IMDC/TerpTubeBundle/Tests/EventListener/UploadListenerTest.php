<?php

namespace IMDC\TerpTubeBundle\Tests\EventListener;

use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\EventListener\UploadListener;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;

/**
 * Class UploadListenerTest
 * @package IMDC\TerpTubeBundle\Tests\EventListener
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UploadListenerTestCase extends MediaTestCase
{
    public function testInstantiate()
    {
        $uploadListener = $this->getUploadListener();

        $this->assertNotNull($uploadListener);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\EventListener\UploadListener', $uploadListener);
    }

    public function testOnUpload_Video()
    {
        $media = $this->createUploadedMedia('video_audio.webm', Media::TYPE_VIDEO);

        $uploadListener = $this->getUploadListener();
        $uploadListener->onUpload(new UploadEvent($media));

        $this->assertNotNull($media->getSourceResource()->getMetaData());

        $this->deleteUploadedMedia($media);
    }

    public function testOnUpload_Image()
    {
        $media = $this->createUploadedMedia('pic1.jpg', Media::TYPE_IMAGE);

        $uploadListener = $this->getUploadListener();
        $uploadListener->onUpload(new UploadEvent($media));

        $resource = $media->getSourceResource();
        $metaData = $resource->getMetaData();
        $imageSize = getimagesize($resource->getAbsolutePath());

        $this->assertTrue(file_exists('web/' . $media->getThumbnailPath()));
        $this->assertGreaterThan(0, filesize('web/' . $media->getThumbnailPath()));

        $this->assertNotNull($metaData);
        $this->assertEquals(filesize($resource->getAbsolutePath()), $metaData->getSize());
        $this->assertEquals($imageSize[0], $metaData->getWidth());
        $this->assertEquals($imageSize[1], $metaData->getHeight());
        $this->assertNull($metaData->getDuration());

        $this->deleteUploadedMedia($media);
    }

    /**
     * @return UploadListener
     */
    private function getUploadListener()
    {
        $logger = $this->container->get('logger');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $multiplexProducer = $this->container->get('old_sound_rabbit_mq.multiplex_producer');
        $transcodeProducer = $this->container->get('old_sound_rabbit_mq.transcode_producer');

        return new UploadListener($logger, $entityManager, $transcoder, $multiplexProducer, $transcodeProducer);
    }
}
