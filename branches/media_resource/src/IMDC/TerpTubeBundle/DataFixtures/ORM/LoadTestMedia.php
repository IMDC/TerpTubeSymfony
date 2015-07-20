<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LoadTestMedia
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestMedia extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_MEDIA_PER_TYPE = 5;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $filesPath;

    /**
     * @var array
     */
    private $resourceFileConfig;

    /**
     * @var Transcoder
     */
    private $transcoder;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if ('test' != $this->container->getParameter('kernel.environment')) {
            return;
        }

        $types = array(
            Media::TYPE_VIDEO,
            //Media::TYPE_AUDIO,
            Media::TYPE_IMAGE,
            //Media::TYPE_OTHER
        );

        $this->filesPath = $this->container->getParameter('imdc_terptube.tests.files_path');
        $this->resourceFileConfig = $this->container->getParameter('imdc_terptube.resource_file');
        $this->transcoder = $this->container->get('imdc_terptube.transcoder');

        foreach ($types as $type) {
            for ($count = 1; $count <= self::NUM_TEST_MEDIA_PER_TYPE; $count++) {
                $title = 'test_media_' . $type . '_' . $count;
                $media = $this->createUploadedMedia($type, $title);
                $manager->persist($media);
                $this->addReference($title, $media);
                //TODO random files based on media type. move flush to end
                $manager->flush();

                if ($type == Media::TYPE_VIDEO || $type == Media::TYPE_AUDIO) {
                    $title = 'test_recorded_tomux_' . $type . '_' . $count;
                    $media = $this->createRecordedMedia($type, $title);
                    $manager->persist($media);
                    $this->addReference($title, $media);
                    //TODO random files based on media type. move flush to end
                    $manager->flush();

                    $title = 'test_recorded_toremux_' . $type . '_' . $count;
                    $media = $this->createRecordedMedia($type, $title, true);
                    $manager->persist($media);
                    $this->addReference($title, $media);
                    //TODO random files based on media type. move flush to end
                    $manager->flush();

                    $title = 'test_transcoded_' . $type . '_' . $count;
                    $media = $this->createTranscodedMedia($type, $title, $manager);
                    $manager->persist($media);
                    $this->addReference($title, $media);
                    //TODO random files based on media type. move flush to end
                    $manager->flush();
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * @param $filesPath
     * @param $filename
     * @return UploadedFile
     */
    public static function createUploadedFile($filesPath, $filename) //TODO make protected
    {
        $filePath = tempnam('/tmp', 'hello_') . $filename;
        copy($filesPath . '/' . $filename, $filePath);

        return new UploadedFile(
            $filePath,
            $filename,
            null,
            filesize($filePath),
            null,
            true
        );
    }

    /**
     * @param $type
     * @param $title
     * @return Media
     */
    protected function createUploadedMedia($type, $title)
    {
        //TODO random files based on media type
        $filename = 'video_audio.webm';
        if ($type == Media::TYPE_IMAGE)
            $filename = 'pic1.jpg';

        $resourceFile = ResourceFile::fromFile(
            $this->createUploadedFile($this->filesPath, $filename),
            $this->resourceFileConfig);
        //TODO use manger to persist so that metadata has actual values
        $resourceFile->updateMetaData($type, $this->transcoder);

        $media = new Media();
        $media->setType($type);
        $media->setTitle($title);
        $media->setState(MediaStateConst::UNPROCESSED);
        $media->setSourceResource($resourceFile);

        return $media;
    }

    /**
     * @param $type
     * @param $title
     * @param bool|false $single
     * @return Media
     */
    protected function createRecordedMedia($type, $title, $single = false)
    {
        $audioFilename = $single ? 'video_audio.webm' : 'audio.wav';

        //TODO random files based on media type
        $video = !$single
            ? $this->createUploadedFile($this->filesPath, 'video.webm')
            : null;
        $audio = $this->createUploadedFile($this->filesPath, $audioFilename);

        if (!$single)
            $video = $video->move('/tmp/terptube-recordings', tempnam('', 'hello_video_') . '.webm');
        $audio = $audio->move('/tmp/terptube-recordings', tempnam('', 'hello_audio_') . ($single ? '.webm' : '.wav'));

        $media = new Media();
        $media->setType($type);
        $media->setTitle(implode('|', array($title, $video ? $video->getPathname() : '', $audio->getPathname())));
        $media->setState(MediaStateConst::UNPROCESSED);

        $resourceFile = ResourceFile::fromFile(
            new IMDCFile(tempnam('/tmp/terptube-recordings', 'hello_')), $this->resourceFileConfig);
        $resourceFile->setPath('placeholder');
        $media->setSourceResource($resourceFile);

        return $media;
    }

    /**
     * @param $type
     * @param $title
     * @return Media
     */
    protected function createTranscodedMedia($type, $title, $manager)
    {
        $media = $this->createUploadedMedia($type, $title);

        // duplicate the source file
        for ($i = 0; $i < 2; $i++) {
            $file = $this->createUploadedFile($this->filesPath, 'video_audio.webm');
            $resource = ResourceFile::fromFile($file, $this->resourceFileConfig);

            $manager->persist($resource);
            $manager->flush();

            $resource->updateMetaData($type, $this->transcoder);
            $media->addResource($resource);
        }

        $media->setState(MediaStateConst::READY);

        return $media;
    }
}
