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
        $this->createMedia('video_audio.webm', Media::TYPE_VIDEO);

        $uploadListener = $this->getUploadListener();
        $uploadListener->onUpload(new UploadEvent($this->media));

        $metaData = $this->media->getMetaData();

        $this->assertNotNull($metaData);
        $this->assertNotNull($metaData->getTimeUploaded());
    }

    public function testOnUpload_Image()
    {
        $this->createMedia('pic1.jpg', Media::TYPE_IMAGE);

        $uploadListener = $this->getUploadListener();
        $uploadListener->onUpload(new UploadEvent($this->media));

        $metaData = $this->media->getMetaData();
        $imageSize = getimagesize($this->media->getResource()->getAbsolutePath());

        $this->assertTrue(file_exists('web/' . $this->media->getThumbnailPath()));
        $this->assertGreaterThan(0, filesize('web/' . $this->media->getThumbnailPath()));

        $this->assertNotNull($metaData);
        $this->assertEquals(filesize($this->media->getResource()->getAbsolutePath()), $metaData->getSize());
        $this->assertEquals($imageSize[0], $metaData->getWidth());
        $this->assertEquals($imageSize[1], $metaData->getHeight());
        $this->assertNull($metaData->getDuration());
        $this->assertNotNull($metaData->getTimeUploaded());
    }

    /**
     * @return UploadListener
     */
    private function getUploadListener()
    {
        $logger = $this->container->get('logger');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $upload_video_producer = $this->container->get('old_sound_rabbit_mq.upload_video_producer');
        $upload_audio_producer = $this->container->get('old_sound_rabbit_mq.upload_audio_producer');
        $transcoder = $this->container->get('imdc_terptube.transcoder');

        return new UploadListener($logger, $entityManager, $upload_video_producer, $upload_audio_producer, $transcoder);
    }
}
