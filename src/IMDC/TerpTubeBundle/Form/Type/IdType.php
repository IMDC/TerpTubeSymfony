<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class IdType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('id', 'integer', array(
            'label' => false,
            'attr' => array(
                'class' => '__name__-id'
            )
        ));
	}

	public function getName()
	{
		return 'id';
	}
}
