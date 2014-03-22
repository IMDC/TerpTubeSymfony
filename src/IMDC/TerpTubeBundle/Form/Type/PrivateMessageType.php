<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;
use IMDC\TerpTubeBundle\Entity\Post;

class PrivateMessageType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$entityManager = $options['em'];
    	$mediaRepository = $entityManager->getRepository('IMDCTerpTubeBundle:Media');
    	$transformer = new UsersToStringsTransformer($entityManager);
    	
        $builder->add(
            $builder->create('recipients', 'text', array('required' => true, 'label' => 'Recipients (use a comma to separate multiple people)'))
                    ->addModelTransformer($transformer)
        );
    	$builder->add('subject');
    	$builder->add('attachedMedia');
    	
//     	$builder->add('attachedMedia', 'collection', array(
//     	    'type' => 'entity',
//     	    'label' => 'Select media to attach',
//     	    'options' => array(
//         	    'class' => 'IMDCTerpTubeBundle:Media',
//         	    'required' => false,
//     	        'choices' => $repository->findAllMediaCreatedByUser($user),
//     	    )
//     	));
        $builder->add('content', 'textarea');
        $builder->add('submit', 'submit', array(
                'attr' => array('class' => 'btn btn-success')
        ));
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
                'user',
        ));
        
        $resolver->setAllowedTypes(array(
        		'em' => 'Doctrine\Common\Persistence\ObjectManager',
                'user' => 'IMDC\TerpTubeBundle\Entity\User',
        ));
        
    }

}