<?php
namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
Use Doctrine\ORM\PersistentCollection;
use IMDC\TerpTubeBundle\Entity\User;

class UsersToStringsTransformer implements DataTransformerInterface
{
    /**
     *
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms a collection of users (User) to a comma separated string of usernames
     * @param DoctrineCollection|null $users
     * @return string
     */
    public function transform($users)
    {
        if (NULL === $users) {
            return "";
        }
        
        $namesArray = array();
        foreach ($users as $user) {
            $namesArray[] = $user->getUsername();
        }
        $names = implode(",", $namesArray);
        
        return $names;
    }

    /**
     * Transforms a string of comma seperated usernames to a collection of Users     
     * @param string usernames
     *
     * @return User|null
     * @throws TransformationFailedException if object (user) not found
     */
    public function reverseTransform($usernames)
    {
        $users = new ArrayCollection();
        
        if (empty($usernames)) {
            return $users;
        }
         
        if (!is_string($usernames)) {
            throw new UnexpectedTypeException($usernames, 'string');
        }
        
        $usernamesArray = explode(",", $usernames);
        foreach($usernamesArray as $username) {
            $user = $this->om->getRepository('IMDCTerpTubeBundle:User')
                        ->findOneBy(array('username' => $username));
            // todo: throw error or message somehow to indicate user not found
            $users->add($user);
        }
        
        return $users;
    }
}