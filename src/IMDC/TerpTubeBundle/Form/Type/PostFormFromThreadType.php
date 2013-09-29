<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\ThreadToNumberTransformer;

class PostFormFromThreadType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $entityManager = $options['em'];
	    $user = $options['user'];
	    $transformer = new ThreadToNumberTransformer($entityManager);
	    
		$builder->add('content');
		$builder->add(
            $builder->create('parentthread', 'hidden')
	            ->addModelTransformer($transformer));
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'PostFormFromThread';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Post',));
		
		$resolver->setRequired(array('em', 'user'));
		
		$resolver->setAllowedTypes(array(
				'em' => 'Doctrine\Common\Persistence\ObjectManager',
				'user' => 'IMDC\TerpTubeBundle\Entity\User',
		));
	}
}