<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ForumFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{    
	    $builder->add('mediatextarea', 'text', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'File',
                                                    'attr' => array('cols' => 1,
                                                                    'rows' => 1)));

	    $builder->add('titleText', null, array('label' => 'Text Title'));
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ForumForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Forum',));
	}
}