<?php
// src/IMDC/TerpTubeBundle/DataFixtures/ORM/LoadNoReplyUserData.php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use IMDC\TerpTubeBundle\Entity\User;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * Class to create a new user in the TerpTube system that acts as the sender for introduction 
 * messages to new users.
 * 
 * This class is used via the command-line using the command
 * `php app/console doctrine:fixtures:load --append`
 * 
 * @author paul
 *
 */
class LoadNoReplyUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = NULL)
    {
        $this->container = $container;
    }
    
    /**
     * Creates a new user in the TerpTube system with username 'noreply'
     * and a randomly generated password, then manually sets their 
     * user id to 0 (which is not used previously)
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        
        $user = $userManager->createUser();
        
        $user->setUsername('noreply');
        
        // create 25 char random password
        $generator = new SecureRandom();
        $user->setPlainPassword($generator->nextBytes(25));
        
        // do not enable this account as no one should ever log in
        $user->setEnabled(false);
        
        $randnum = rand(0, 10000);
        $adminemail = 'noreply-' . $randnum;
        $user->setEmail($adminemail);
        
        $user->addRole('ROLE_NO_REPLY');
        
        $userManager->updatePassword($user);
        $userManager->updateUser($user);
        
        $manager->persist($user);
        $manager->flush();
        
        $stmt = $manager->getConnection()
                    ->prepare("UPDATE fos_user set id=0 where email=? LIMIT 1");
        $stmt->bindParam(1, $adminemail);
        $stmt->execute();
        
        //return $stmt->rowCount();
        
    }
}