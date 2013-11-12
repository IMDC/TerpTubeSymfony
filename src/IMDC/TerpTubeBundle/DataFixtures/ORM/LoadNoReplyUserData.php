<?php
// src/IMDC/TerpTubeBundle/DataFixtures/ORM/LoadNoReplyUserData.php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use IMDC\TerpTubeBundle\Entity\User;

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
    
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        
        $user = $userManager->createUser();
        
        $user->setUsername('noreply');
        $user->setPlainPassword('7980fau!%#!FDSkdf9813hkladf89FDASJ#H!%a8s');
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