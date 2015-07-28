<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\InvitationType;

class LoadInvitationTypes implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = array(
            array(InvitationType::TYPE_MENTOR, 'Mentor'),
            array(InvitationType::TYPE_MENTEE, 'Mentee'),
            array(InvitationType::TYPE_GROUP, 'Group')
        );

        foreach ($types as $type) {
            $exists = $manager->getRepository('IMDCTerpTubeBundle:InvitationType')->find($type[0]);
            if ($exists) {
                continue;
            }

            $invitationType = new InvitationType();
            $invitationType->setId($type[0]);
            $invitationType->setName($type[1]);

            $manager->persist($invitationType);
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
