<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\AccessType;

/**
 * Class AccessTypes
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessTypes implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = [
            [AccessType::TYPE_PUBLIC, 'Public'],
            [AccessType::TYPE_LINK_ONLY, 'Link only'],
            [AccessType::TYPE_REGISTERED_USERS, 'Registered users'],
            [AccessType::TYPE_USERS, 'Specific users'],
            [AccessType::TYPE_FRIENDS, 'My friends'],
            [AccessType::TYPE_GROUP, 'A specific group'],
            [AccessType::TYPE_PRIVATE, 'Private']];

        foreach ($types as $type) {
            $exists = $manager->getRepository('IMDCTerpTubeBundle:AccessType')->find($type[0]);
            if ($exists) {
                continue;
            }

            $invitationType = new AccessType();
            $invitationType->setId($type[0]);
            $invitationType->setDescription($type[1]);

            $manager->persist($invitationType);
        }

        $manager->flush();
    }
}
