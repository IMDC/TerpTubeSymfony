<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThreadFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $builder->add('title');
		$builder->add('content');
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ThreadForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Thread',));
	}
}