<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageReplyType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;
use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\Post;

class PrivateMessageReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];
        $repository = $entityManager->getRepository('IMDCTerpTubeBundle:Media');
        $transformer = new UsersToStringsTransformer($entityManager);
        $user = $options['user'];
        
        $builder->add(
            $builder->create('recipients', 'text', array('required' => true, 'label' => 'Recipients (use a comma to separate multiple people)'))
                    ->addModelTransformer($transformer)
        );
    	$builder->add('subject');
        $builder->add('attachedMedia');
    	
    	/*$builder->add('attachedMedia', 'collection', array(
    	    'type' => 'entity',
    	    'allow_add' => true,
    	    'allow_delete' => true,
    	    'prototype' => true,
    	    'options' => array(
	            'label' => ' ',
    	        'class' => 'IMDCTerpTubeBundle:Media',
    	        'data' => 0,
    	        'required' => false,
    	        'empty_value' => 'Choose an option',
    	        'choices' => $repository->findAllMediaCreatedByUser($user),
    	    )
	    ));*/
    	
        $builder->add('content', 'textarea');
        $builder->add('submit', 'submit');
    }
    
    public function getName()
    {
        return 'PrivateMessageReplyForm';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Message',));
        
        $resolver->setRequired(array(
        		'em',
                'user',
        ));
        
        $resolver->setAllowedTypes(array(
        		'em' => 'Doctrine\Common\Persistence\ObjectManager',
                'user' => 'IMDC\TerpTubeBundle\Entity\User',
        ));
        
    }
}