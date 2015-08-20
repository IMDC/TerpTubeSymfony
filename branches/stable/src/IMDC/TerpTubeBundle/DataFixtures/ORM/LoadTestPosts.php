<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\Post;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTestPosts
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestPosts extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_POSTS = 5;

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

        for ($count = 1; $count <= self::NUM_TEST_POSTS; $count++) {
            $currentDate = new \DateTime();

            $post = new Post();
            $post->setCreated($currentDate);
            $post->setIsTemporal(true);

            $manager->persist($post);

            $this->addReference('test_post_' . $count, $post);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6;
    }
}
