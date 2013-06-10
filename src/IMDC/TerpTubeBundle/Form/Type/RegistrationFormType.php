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
        $builder->add('firstName', null, array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'));
       // $builder->add('firstName');
		//$builder->add('lastName');
		$builder->add('lastName', null, array('label' => 'form.lastName', 'translation_domain' => 'FOSUserBundle'));
    }

    public function getName()
    {
        return 'imdc_terptube_user_registration';
    }
}
?>