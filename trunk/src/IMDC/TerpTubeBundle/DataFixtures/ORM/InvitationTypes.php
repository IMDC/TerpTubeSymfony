<?php

namespace IMDC\TerpTubeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\InvitationType;

class InvitationTypes implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = [
            [InvitationType::TYPE_MENTOR, 'Mentor'],
            [InvitationType::TYPE_MENTEE, 'Mentee'],
            [InvitationType::TYPE_GROUP, 'Group']];

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
}
