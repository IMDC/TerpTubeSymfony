<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager;
use IMDC\TerpTubeBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;

/**
 * Class LoadTestUsers
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadTestUsers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const NUM_TEST_USERS = 5;

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

        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        for ($count = 1; $count <= self::NUM_TEST_USERS; $count++) {
            $username = 'test_user_' . $count;

            /** @var User $user */
            $user = $userManager->createUser();
            $user->setUsername($username);
            $user->setEmail($username . '@example.com');
            $user->setPlainPassword('test');
            $user->setEnabled(true);
            $user->addRole('ROLE_CREATE_FORUMS');

            $profile = $user->getProfile();
            $profile->setFirstName($username);
            $profile->setLastName($username);
            $profile->setCity('Toronto');
            $profile->setCountry(Intl::getRegionBundle()->getCountryName('CA'));

            $userManager->updateUser($user, false);

            $manager->persist($user);

            $this->addReference($username, $user);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
