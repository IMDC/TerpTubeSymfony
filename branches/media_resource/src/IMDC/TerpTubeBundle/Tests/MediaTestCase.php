<?php

namespace IMDC\TerpTubeBundle\Tests;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MediaTestCase
 * @package IMDC\TerpTubeBundle\Tests
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MediaTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param $fileName
     * @return UploadedFile
     * @deprecated
     */
    protected function createUploadedFile($fileName) //TODO moved to fixtures
    {
        $rootDir = static::$kernel->getRootDir() . '/';
        $filePath = '/tmp/' . rand() . $fileName;
        copy($rootDir . '../../test_files/' . $fileName, $filePath);

        return new UploadedFile(
            $filePath,
            $fileName,
            null,
            filesize($filePath),
            null,
            true
        );
    }

    /**
     * @param $filename
     * @param $type
     * @return Media
     */
    protected function createUploadedMedia($filename, $type)
    {
        $file = $this->createUploadedFile($filename);
        $resourceFile = ResourceFile::fromFile($file);

        $media = new Media();
        $media->setType($type);
        $media->setTitle('test:' . rand());
        $media->setState(MediaStateConst::UNPROCESSED);
        $media->setSourceResource($resourceFile);

        $this->entityManager->persist($resourceFile);
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }

    /**
     * @param $videoFilename
     * @param $audioFilename
     * @param bool $isFirefox
     * @return array
     */
    protected function createRecordedMedia($videoFilename, $audioFilename, $isFirefox = false)
    {
        $video = !$isFirefox
            ? $this->createUploadedFile($videoFilename)
            : null;
        $audio = $this->createUploadedFile($audioFilename);

        if (!$isFirefox)
            $video = $video->move('/tmp/terptube-recordings', tempnam('', 'hello_video_') . '.webm');
        $audio = $audio->move('/tmp/terptube-recordings', tempnam('', 'hello_audio_') . ($isFirefox ? '.webm' : '.wav'));

        $media = new Media();
        $media->setType(Media::TYPE_VIDEO);
        $media->setTitle('test:' . rand());
        $media->setState(MediaStateConst::UNPROCESSED);

        $resourceFile = ResourceFile::fromFile(new IMDCFile(tempnam('/tmp/terptube-recordings', 'hello_')));
        $resourceFile->setPath('placeholder');
        $media->setSourceResource($resourceFile);

        $this->entityManager->persist($resourceFile);
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return array($video, $audio, $media);
    }

    protected function createTranscodedMedia($filename)
    {
        $media = $this->createUploadedMedia($filename, Media::TYPE_VIDEO);
        /** @var Transcoder $transcoder */
        $transcoder = $this->container->get('imdc_terptube.transcoder');

        // duplicate the source file
        for ($i = 0; $i < 2; $i++) {
            $file = $this->createUploadedFile($filename);
            $resource = ResourceFile::fromFile($file);

            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $resource->updateMetaData(Media::TYPE_VIDEO, $transcoder);
            $media->addResource($resource);
        }

        $media->setState(MediaStateConst::READY);

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }

    protected function deleteUploadedMedia($media)
    {
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    protected function deleteRecordedMedia($recording)
    {
        /** @var UploadedFile $video */
        $video = $recording[0];
        /** @var UploadedFile $audio */
        $audio = $recording[1];
        /** @var Media $media */
        $media = $recording[2];

        try {
            if ($video) unlink($video->getPathname());
        } catch (\Exception $ex) {
        }
        try {
            if ($audio) unlink($audio->getPathname());
        } catch (\Exception $ex) {
        }

        $this->deleteUploadedMedia($media);
    }

    protected function deleteTranscodedMedia($media)
    {
        $this->deleteUploadedMedia($media);
    }
}
