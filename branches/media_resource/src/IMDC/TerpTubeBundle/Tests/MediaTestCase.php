<?php

namespace IMDC\TerpTubeBundle\Tests;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
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

    /**
     * @var Media
     */
    protected $media;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    public function tearDown()
    {
        $this->deleteMedia();
    }

    /**
     * @param $fileName
     * @return UploadedFile
     */
    protected function createUploadedFile($fileName)
    {
        $rootDir = static::$kernel->getRootDir() . '/';
        $filePath = '/tmp/' . $fileName;
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
     * @param $fileName
     * @param $type
     */
    protected function createMedia($fileName, $type)
    {
        $file = $this->createUploadedFile($fileName);

        $resourceFile = new ResourceFile();
        $resourceFile->setFile($file);
        //$resourceFile->setWebmExtension('webm');

        $this->media = new Media();
        //$this->media->setOwner($user);
        $this->media->setType($type);
        $this->media->setTitle('test:' . rand());
        $this->media->setIsReady(Media::READY_WEBM);
        $this->media->setResource($resourceFile);

        $this->entityManager->persist($resourceFile);
        $this->entityManager->persist($this->media);
        $this->entityManager->flush();
    }

    protected function deleteMedia()
    {
        if (!$this->media) {
            return;
        }

        $this->entityManager->remove($this->media);
        $this->entityManager->flush();
    }
}
