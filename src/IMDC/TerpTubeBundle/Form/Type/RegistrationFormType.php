<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->add('profile', new RegistrationProfileFormType('User'), array('label'=>' ', 'translation_domain' => 'IMDCTerpTubeBundle'));
        
		
    }

    
    public function getName()
    {
        return 'imdc_terptube_user_registration';
    }
    
}
?>