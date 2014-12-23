<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class IdType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @deprecated
 */
class IdType extends AbstractType //TODO delete
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
