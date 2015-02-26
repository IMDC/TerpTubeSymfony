<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Security\Acl\AccessChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AccessTypeType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessTypeType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$class = $options['class'];
        $hasValidGroup = $options['hasValidGroup'];

        $qb = $this->entityManager->getRepository('IMDCTerpTubeBundle:AccessType')->createQueryBuilder('a');
        if ($class == 'IMDC\TerpTubeBundle\Entity\Thread') {
            $qb->where('a.id != :accessType')
                ->setParameter('accessType', AccessType::TYPE_GROUP);
        }
        $builder->add('_', 'entity', array(
            'class' => 'IMDCTerpTubeBundle:AccessType',
            'query_builder' => $qb,
            'expanded' => true,
            'data' => $this->entityManager->getReference('IMDCTerpTubeBundle:AccessType', $hasValidGroup ? AccessType::TYPE_GROUP : AccessType::TYPE_PUBLIC),
            'label' => false,
            'property_path' => 'id'
        ));*/
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $em = $this->entityManager;

        $choiceList = function (Options $options) use ($em) {
            return AccessChoiceList::fromEntityManager($em, $options['class']);
        };

        $resolver
            ->setDefaults(array(
                'choice_list' => $choiceList,
                'expanded' => true))
            ->setRequired(array(
                'class'))
            ->addAllowedValues(array(
                'class' => array(
                    'IMDC\TerpTubeBundle\Entity\Forum',
                    'IMDC\TerpTubeBundle\Entity\Thread')));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'access_type';
    }
}
