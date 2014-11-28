<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class ThreadFormDeleteType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @deprecated
 */
class ThreadFormDeleteType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    $thread = $builder->getData();
	    
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ThreadDeleteForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Thread',));
		
		/*
		$resolver->setRequired(array(
		        'user',
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		));
		*/
	}
}