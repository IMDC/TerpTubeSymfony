<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;

class PrivateMessageType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$entityManager = $options['em'];
    	$transformer = new UsersToStringsTransformer($entityManager);
    	
        $builder->add(
            $builder->create('recipients', 'text', array('required' => true, 'label' => 'Recipients (use a comma to separate multiple people)'))
                    ->addModelTransformer($transformer)
        );
    	$builder->add('subject');
        $builder->add('content', 'textarea');
        $builder->add('submit', 'submit');
    }
    
    public function getName()
    {
        return 'PrivateMessageForm';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Message',));
        
        
        $resolver->setRequired(array(
        		'em',
        ));
        
        $resolver->setAllowedTypes(array(
        		'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
        
    }

}