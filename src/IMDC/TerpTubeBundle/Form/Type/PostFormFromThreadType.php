<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\DataTransformer\ThreadToNumberTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class PostFormFromThreadType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $entityManager = $options['em'];
	    $user = $options['user'];
	    $thread = $options['thread'];
	    $transformer = new ThreadToNumberTransformer($entityManager);
	    
	    $builder->add('mediatextarea', 
	    				'text', 
	    				array('required' => false,
	    						'mapped' => false,
					    		'read_only' => true,
					    		'label' => 'Attached File',
			    		)
    	);
	    
		$builder->add('content');
		$builder->add(
            $builder->create('parentthread', 'hidden')
	            ->addModelTransformer($transformer));
		
		if ($thread->getType() == 1) {
    		$builder->add('startTime', 'number', array(
					'required' => false,
                    'precision' => 2,
    		));
    		
    		$builder->add('endTime', 'number', array(
    				'required' => false,
    		        'precision' => 2,
    		));
		}
		
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'PostFormFromThread';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Post',));
		
		$resolver->setRequired(array('em', 'user', 'thread'));
		
		$resolver->setAllowedTypes(array(
				'em' => 'Doctrine\Common\Persistence\ObjectManager',
				'user' => 'IMDC\TerpTubeBundle\Entity\User',
		        'thread' => 'IMDC\TerpTubeBundle\Entity\Thread',
		));
	}
}