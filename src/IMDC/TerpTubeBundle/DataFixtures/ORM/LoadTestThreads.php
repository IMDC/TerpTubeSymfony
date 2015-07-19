<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Thread;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTestThreads
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestThreads extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_THREADS = 5;

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

        for ($count = 1; $count <= self::NUM_TEST_THREADS; $count++) {
            $title = 'test_thread_' . $count;
            $currentDate = new \DateTime();
            $accessType = $manager->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_PUBLIC);

            $thread = new Thread();
            $thread->setTitle($title);
            $thread->setCreationDate($currentDate);
            $thread->setLocked(false);
            $thread->setSticky(false);
            $thread->setAccessType($accessType);

            $manager->persist($thread);

            $this->addReference($title, $thread);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
