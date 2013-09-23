<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThreadFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    // this assumes that the entity manager was passed in as an option
	    //$entityManager = $options['em'];
	    //$transformer = new MediaToStringTransformer($entityManager);
	    
	    
	    
	    
	    $builder->add('title');
	    //$builder->add('mediaIncluded');
	     
	    //add a normal text field, but add your transformer to it
	    /*$builder->add(
	            $builder->create('mediaIncluded', 'text')
	            ->addModelTransformer($transformer)
	    );*/
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
		/*
		$resolver->setRequired(array(
		        'em',
		));
		
		$resolver->setAllowedTypes(array(
		        'em' => 'Doctrine\Common\Persistence\ObjectManager',
		));
		*/
	}
}