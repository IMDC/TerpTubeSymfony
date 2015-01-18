<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserGroupType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $options['em'];

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
        $builder->add('membersCanAddForums', 'checkbox', array('required' => false));

        /*$builder->add('members', new UsersSelectType(), array(
            'em' => $em,
            'mapped' => false,
            'required' => false
        ));*/

        $builder->add(
            $builder
                ->create('members', 'text', array(
                    'mapped' => false,
                    'required' => false
                ))
                ->addModelTransformer(new UsersToStringsTransformer($em))
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'IMDC\TerpTubeBundle\Entity\UserGroup'))
            ->setRequired(array(
                'em'))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager'
            ));
    }
    
    public function getName()
    {
        return 'user_group';
    }
}
