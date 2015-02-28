<?php

namespace IMDC\TerpTubeBundle\Security\Acl;

use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

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
     * @param $entityManager
     * @param $class
     * @return AccessChoiceList
     */
    public static function fromEntityManager($entityManager, $class)
    {
        $qb = $entityManager->getRepository('IMDCTerpTubeBundle:AccessType')->createQueryBuilder('a');

        if ($class == 'IMDC\TerpTubeBundle\Entity\Thread') {
            // threads do not support group access type
            $qb->where('a.id != :accessType')
                ->setParameter('accessType', AccessType::TYPE_GROUP);
        }

        $accessTypes = $qb->getQuery()->getResult();

        return new self($accessTypes);
    }
}
