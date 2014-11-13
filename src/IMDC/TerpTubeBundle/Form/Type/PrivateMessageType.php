<?php

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

        $builder->add('mediatextarea', 'media_chooser');
    	
        $builder->add(
            $builder
                ->create('recipients', 'text', array(
                    'label' => 'Recipients (comma separated list of usernames)'))
                ->addModelTransformer($transformer)
        );

    	$builder->add('subject', 'text', array('required' => true));

        $builder->add('content', 'textarea', array(
            'attr' => array(
                'class' => 'autosize')
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Message'))
            ->setRequired(array(
                'em'))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager'
        ));
    }
    
    public function getName()
    {
        return 'PrivateMessageForm';
    }
}