<?php

namespace IMDC\TerpTubeBundle\Security\Acl;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AccessChoiceList
 * @package IMDC\TerpTubeBundle\Security\Acl
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessChoiceList extends ObjectChoiceList
{
    /**
     * @param array $choices
     */
    public function __construct(array $choices)
    {
        parent::__construct($choices, null, array(), null, 'id');
    }

    /**
     * @param EntityManager $entityManager
     * @param $class
     * @param SecurityContext $securityContext
     * @return AccessChoiceList
     */
    public static function fromEntityManager(EntityManager $entityManager, $class, SecurityContext $securityContext)
    {
        $qb = $entityManager->getRepository('IMDCTerpTubeBundle:AccessType')->createQueryBuilder('a');
        $restricted = array();

        if ($class == 'IMDC\TerpTubeBundle\Entity\Forum') {
            // restrict if user cannot create top level forums
            if ($securityContext->isGranted('ROLE_CREATE_FORUMS') === false) {
                array_push($restricted,
                    AccessType::TYPE_PUBLIC,
                    AccessType::TYPE_LINK_ONLY,
                    AccessType::TYPE_REGISTERED_USERS,
                    AccessType::TYPE_USERS,
                    AccessType::TYPE_FRIENDS,
                    AccessType::TYPE_PRIVATE
                );
            }
        }

        if ($class == 'IMDC\TerpTubeBundle\Entity\Thread') {
            // threads do not support group access type
            $restricted[] = AccessType::TYPE_GROUP;
        }

        // Expr\NotIn doesn't like empty arrays
        if (!empty($restricted)) {
            $qb = $qb->where($qb->expr()->notIn('a.id', ':restricted'))
                ->setParameter('restricted', $restricted);
        }

        $accessTypes = $qb->getQuery()->getResult();

        return new self($accessTypes);
    }
}
