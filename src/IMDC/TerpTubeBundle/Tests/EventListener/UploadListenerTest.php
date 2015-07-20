<?php

namespace IMDC\TerpTubeBundle\Tests\EventListener;

use IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\EventListener\UploadListener;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;

/**
 * Class UploadListenerTest
 * @package IMDC\TerpTubeBundle\Tests\EventListener
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UploadListenerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $uploadListener = $this->getUploadListener();

        $this->assertNotNull($uploadListener);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\EventListener\UploadListener', $uploadListener);
    }

    public function testOnUpload_Video()
    {
        //$media = $this->createUploadedMedia('video_audio.webm', Media::TYPE_VIDEO);
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_media_1_1');

        $uploadListener = $this->getUploadListener();
        $uploadListener->onUpload(new UploadEvent($media));

        $this->assertNotNull($media->getSourceResource()->getMetaData());
    }

    public function testOnUpload_Image()
    {
        //$media = $this->createUploadedMedia('pic1.jpg', Media::TYPE_IMAGE);
        /** @var Media $media */
        $media = $this->referenceRepo->getReference('test_media_0_1');

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
    }

    /**
     * @return UploadListener
     */
    private function getUploadListener()
    {
        $logger = $this->getContainer()->get('logger');
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        $multiplexProducer = $this->getContainer()->get('old_sound_rabbit_mq.multiplex_producer');
        $transcodeProducer = $this->getContainer()->get('old_sound_rabbit_mq.transcode_producer');

        return new UploadListener($logger, $entityManager, $transcoder, $multiplexProducer, $transcodeProducer);
    }
}
