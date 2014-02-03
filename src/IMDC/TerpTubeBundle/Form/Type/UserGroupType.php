<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Entity\Forum;

class UserGroupType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('name', null, array('label' => 'Group name:'));
    	$builder->add('visibleToPublic', 'checkbox', array('data' => TRUE));
    	$builder->add('visibleToRegisteredUsers', 'checkbox', array('data' => TRUE));
    	$builder->add('openForNewMembers', 'checkbox', array('data' => TRUE));
    	$builder->add('joinByInvitationOnly');
        $builder->add('submit', 'submit');
        $builder->add('userGroupForum', new ForumFormType());
    }
    
    public function getName()
    {
        return 'UserGroupForm';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\UserGroup',));
    }
}