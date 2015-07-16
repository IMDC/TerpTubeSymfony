<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTestForums
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestForums extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_FORUMS = 5;

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

        for ($count = 1; $count <= self::NUM_TEST_FORUMS; $count++) {
            $title = 'test_forum_' . $count;
            $currentDate = new \DateTime();
            //$user = $this->getReference('test_user_1');
            $accessType = $manager->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_PUBLIC);

            $forum = new Forum();
            $forum->setTitleText($title);
            $forum->setLastActivity($currentDate);
            $forum->setCreationDate($currentDate);
            //$forum->setCreator($user);
            $forum->setAccessType($accessType);

            $manager->persist($forum);

            $this->addReference($title, $forum);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
