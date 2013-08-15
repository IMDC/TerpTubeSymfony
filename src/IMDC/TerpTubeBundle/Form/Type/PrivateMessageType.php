<?php

// src/IMDC/TerpTubeBundle/Form/Type/PrivateMessageType.php
namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PrivateMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('recipients');
    	$builder->add('recips', null, array('mapped' => false));
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
    }
}