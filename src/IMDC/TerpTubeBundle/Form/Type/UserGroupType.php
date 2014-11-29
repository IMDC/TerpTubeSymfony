<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserGroupType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('media', 'media_chooser');

    	$builder->add('name', 'text', array(
            'label' => 'Group name'
        ));

        $builder->add('description', 'textarea', array(
            'required' => false,
            'attr' => array(
                'class' => 'autosize')
        ));

    	$builder->add('visibleToPublic', 'checkbox', array('required' => false));
    	$builder->add('visibleToRegisteredUsers', 'checkbox', array('data' => true, 'required' => false));
    	$builder->add('openForNewMembers', 'checkbox', array('data' => true, 'required' => false));
    	$builder->add('joinByInvitationOnly', 'checkbox', array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\UserGroup'
        ));
    }
    
    public function getName()
    {
        return 'UserGroupForm';
    }
}
