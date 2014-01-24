<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PermissionsType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('privateAccess')
            ->add('usersWithLinkHaveAccess')
            ->add('usersWithAccess')
            ->add('groupsWithAccess')
            ->add('publicAccess')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Permissions'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'imdc_terptubebundle_permissions';
    }
}
