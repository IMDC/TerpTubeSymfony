<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use IMDC\TerpTubeBundle\Form\DataTransformer\UserCollectionToIntArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsersSelectType extends AbstractType
{
    private $name;

    public function __construct($name)
    {
        $this->name = !empty($name) ? $name : 'users_select';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];

        $builder->add(
            $builder
                ->create('users', 'collection', array(
                    'type' => 'hidden',
                    'label' => false,
                    'allow_add' => true,
                    'attr' => array(
                        'style' => 'display: none;'
                    )))
                ->addModelTransformer(new UserCollectionToIntArrayTransformer($entityManager))
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'em'))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager'
            ));
    }

    public function getName()
    {
        return $this->name;
    }
}
