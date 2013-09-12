<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageReplyType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PrivateMessageReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('recipients');
    	$builder->add('to', null, array('label' => 'To (separate people with a space)', 'mapped' => false));
    	$builder->add('subject');
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
    }
}