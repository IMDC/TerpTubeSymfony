<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class ForumFormDeleteType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @deprecated
 */
class ForumFormDeleteType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    $builder->add('titleText', null, array('label' => 'Text Title', 
	                                           'disabled' => true
	    ));

		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ForumDeleteForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Forum',));
		
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