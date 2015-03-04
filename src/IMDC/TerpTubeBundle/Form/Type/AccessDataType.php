<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AccessDataType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessDataType extends AbstractType
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
        $attr = array('style' => 'display: none;');

        $builder->add(
            $builder
                ->create('users', 'text', array(
                    'label_attr' => $attr,
                    'required' => false,
                    'attr' => $attr
                ))
                ->addModelTransformer(new UsersToStringsTransformer($this->entityManager))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'access_data';
    }
}
