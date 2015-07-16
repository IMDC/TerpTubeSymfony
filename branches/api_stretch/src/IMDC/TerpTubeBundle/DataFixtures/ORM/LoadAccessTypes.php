<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\AccessType;

/**
 * Class LoadAccessTypes
 * @package IMDC\TerpTubeBundle\DataFixtures\ORM
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class LoadAccessTypes implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = array(
            array(AccessType::TYPE_PUBLIC, 'Public'),
            array(AccessType::TYPE_LINK_ONLY, 'Link only'),
            array(AccessType::TYPE_REGISTERED_USERS, 'Registered users'),
            array(AccessType::TYPE_USERS, 'Specific users'),
            array(AccessType::TYPE_FRIENDS, 'My friends'),
            array(AccessType::TYPE_GROUP, 'A specific group'),
            array(AccessType::TYPE_PRIVATE, 'Private')
        );

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

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
