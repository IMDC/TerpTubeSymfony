<?php

namespace IMDC\TerpTubeBundle\Utils;

use FOS\UserBundle\Model\UserManagerInterface;
use IMDC\TerpTubeBundle\Entity\UserProfile;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Executes some manipulations on the users
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Luis Cordova <cordoval@gmail.com>
 */
class UserManipulator
{
    /**
     * User manager
     *
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string  $username
     * @param string  $password
     * @param string  $email
     * @param string  $firstname
     * @param string  $lastname
     * @param string  $city
     * @param string  $country
     * @param Boolean $active
     * @param Boolean $superadmin
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function create($username, $password, $email, $firstname, $lastname, $city, $country, $active, $superadmin)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((Boolean)$active);
        $user->setSuperAdmin((Boolean)$superadmin);
        //$this->userManager->updateUser($user);

        $newprofile = new UserProfile();
        $newprofile->setFirstName($firstname);
        $newprofile->setLastName($lastname);
        $newprofile->setCity($city);
        $newprofile->setCountry($country);
        
        $newprofile->setProfileVisibleToPublic(TRUE);
        
        $user->setProfile($newprofile);
        $this->userManager->updateUser($user, TRUE);
        
        return $user;
    }
}