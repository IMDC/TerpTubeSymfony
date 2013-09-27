<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\UserToStringTransformer;

class PrivateMessageType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	// this assumes that the entity manager was passed in as an option
    	//$entityManager = $options['em'];
    	//$transformer = new UserToStringTransformer($entityManager);
    	
        //$builder->addEventSubscriber(new AddToFieldSubscriber());
        
        //$builder->add('recipients');
    	$builder->add('to', null, array('label' => 'To (separate people with a space)', 'mapped' => false));
    	/*
    	$builder->add($builder->create('recipients', 'text')
    				->addModelTransformer($transformer));
    	*/
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
        
        /*
        $resolver->setRequired(array(
        		'em',
        ));
        
        $resolver->setAllowedTypes(array(
        		'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
        */
    }

}