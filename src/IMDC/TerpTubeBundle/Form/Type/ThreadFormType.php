<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThreadFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    $builder->add('title', null, array('label' => 'Text Title'));
	    //$builder->add('mediaIncluded');
	    
	    // this assumes that the entity manager was passed in as an option
	    /*
	    $entityManager = $options['em'];
	    $transformer = new MediaToStringTransformer($entityManager);
	    */
	    //add a normal text field, but add your transformer to it
	    /*
	    $builder->add(
	            $builder->create('mediaIncluded', 'text')
	            ->addModelTransformer($transformer), array('required'=>false)
	    );
	    */
	    /*
	    $builder->add('mediaID', 'hidden', array('label' => 'Media ID', 'mapped' => false, 
	                                                'required'=> false, 
	                                                'attr' => array('data-mid' => 0)));
	                                                */
	    $builder->add('mediatextarea', 'textarea', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'File',
                                                    'attr' => array('cols' => 1,
                                                                    'rows' => 1)));
	    $builder->add('content', null, array('label' => 'Supplementary Content'));
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