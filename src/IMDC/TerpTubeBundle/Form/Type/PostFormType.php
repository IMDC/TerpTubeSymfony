<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\ThreadToNumberTransformer;

class PostFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $entityManager = $options['em'];
	    $transformer = new ThreadToNumberTransformer($entityManager);
	    
		$builder->add('content');
		$builder->add(
            $builder->create('parentthread', 'hidden')
	            ->addModelTransformer($transformer));
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'PostForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Post',));
		
		$resolver->setRequired(array('em'));
		
		$resolver->setAllowedTypes(array('em' => 'Doctrine\Common\Persistence\ObjectManager',));
	}
}