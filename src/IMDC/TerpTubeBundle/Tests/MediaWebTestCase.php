<?php

namespace IMDC\TerpTubeBundle\Tests;

use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;

/**
 * Class MediaWebTestCase
 * @package IMDC\TerpTubeBundle\Tests
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 * @deprecated
 */
class MediaWebTestCase extends BaseWebTestCase //TODO delete
{
    /**
     * @param $filename
     * @param $type
     * @return Media
     */
    protected function createUploadedMedia($filename, $type)
    {
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();

        $file = LoadTestMedia::createUploadedFile($filesPath, $filename);
        $resourceFile = ResourceFile::fromFile($file, $resourceFileConfig);

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
     * @param bool|false $isFirefox
     * @return array
     */
    protected function createRecordedMedia($videoFilename, $audioFilename, $isFirefox = false)
    {
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();

        $video = !$isFirefox
            ? LoadTestMedia::createUploadedFile($filesPath, $videoFilename)
            : null;
        $audio = LoadTestMedia::createUploadedFile($filesPath, $audioFilename);

        if (!$isFirefox)
            $video = $video->move('/tmp/terptube-recordings', tempnam('', 'hello_video_') . '.webm');
        $audio = $audio->move('/tmp/terptube-recordings', tempnam('', 'hello_audio_') . ($isFirefox ? '.webm' : '.wav'));

        $media = new Media();
        $media->setType(Media::TYPE_VIDEO);
        $media->setTitle('test:' . rand());
        $media->setState(MediaStateConst::UNPROCESSED);

        $resourceFile = ResourceFile::fromFile(
            new IMDCFile(tempnam('/tmp/terptube-recordings', 'hello_')), $resourceFileConfig);
        $resourceFile->setPath('placeholder');
        $media->setSourceResource($resourceFile);

        $this->entityManager->persist($resourceFile);
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return array($video, $audio, $media);
    }

    /**
     * @param $filename
     * @return Media
     */
    protected function createTranscodedMedia($filename)
    {
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $resourceFileConfig = $this->getContainer()->getParameter('imdc_terptube.resource_file');
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();

        $media = $this->createUploadedMedia($filename, Media::TYPE_VIDEO);
        /** @var Transcoder $transcoder */
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');

        // duplicate the source file
        for ($i = 0; $i < 2; $i++) {
            $file = LoadTestMedia::createUploadedFile($filesPath, $filename);
            $resource = ResourceFile::fromFile($file, $resourceFileConfig);

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
}
