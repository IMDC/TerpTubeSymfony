<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\GroupManager;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTestGroups
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestGroups extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_GROUPS = 5;

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

        /** @var GroupManager $groupManager */
        $groupManager = $this->container->get('fos_user.group_manager');

        for ($count = 1; $count <= self::NUM_TEST_GROUPS; $count++) {
            $name = 'test_group_' . $count;

            /** @var UserGroup $group */
            $group = $groupManager->createGroup($name);

            $groupManager->updateGroup($group, false);

            $manager->persist($group);

            $this->addReference($name, $group);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
