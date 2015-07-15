<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
            /*Media::TYPE_AUDIO,
            Media::TYPE_IMAGE,
            Media::TYPE_OTHER*/
        );

        $resourceFileConfig = $this->container->getParameter('imdc_terptube.resource_file');
        /** @var Transcoder $transcoder */
        $transcoder = $this->container->get('imdc_terptube.transcoder');

        foreach ($types as $type) {
            for ($count = 1; $count <= self::NUM_TEST_MEDIA_PER_TYPE; $count++) {
                $title = 'test_media_' . $count;
                //$user = $this->getReference('test_user_1');// . rand(1, LoadTestUsers::NUM_TEST_USERS));
                //TODO random files based on media type. move flush to end
                $resourceFile = ResourceFile::fromFile(
                    $this->createUploadedFile('case03_video_698.733333s_480p_500k.webm'), $resourceFileConfig);
                $resourceFile->updateMetaData($type, $transcoder);

                $media = new Media();
                $media->setType($type);
                $media->setTitle($title);
                $media->setState(MediaStateConst::UNPROCESSED);
                $media->setSourceResource($resourceFile);
                //$media->setOwner($user);

                $manager->persist($media);

                $this->addReference($title, $media);

                //TODO random files based on media type. move flush to end
                $manager->flush();
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
     * @param $fileName
     * @return UploadedFile
     */
    private function createUploadedFile($fileName)
    {
        $filePath = '/tmp/' . $fileName;
        copy($this->container->getParameter('imdc_terptube.tests.files_path') . '/' . $fileName, $filePath);

        return new UploadedFile(
            $filePath,
            $fileName,
            null,
            filesize($filePath),
            null,
            true
        );
    }
}
