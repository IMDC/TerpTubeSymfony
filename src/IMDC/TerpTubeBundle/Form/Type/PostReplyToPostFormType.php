<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
// use IMDC\TerpTubeBundle\Form\DataTransformer\ThreadToNumberTransformer;

class PostReplyToPostFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
// 	    $entityManager = $options['em'];
// 	    $transformer = new ThreadToNumberTransformer($entityManager);
	    
	    $builder->add('mediatextarea',
	        'text',
	        array('required' => false,
	            'mapped' => false,
	            'read_only' => true,
	            'label' => 'Attached File',
	        )
	    );
	    
		$builder->add('content');
		
		// not adding parent post here, do I need it?
		// $builder->add('parentPost', 'hidden');

// 		$builder->add(
//             $builder->create('parentPost', 'hidden')
// 	            ->addModelTransformer($transformer));
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'PostReplyToPostForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Post',));
		
// 		$resolver->setRequired(array('em'));
		
// 		$resolver->setAllowedTypes(array('em' => 'Doctrine\Common\Persistence\ObjectManager',));
	}
}