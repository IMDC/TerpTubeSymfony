<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PostEditFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    // User type
	    $user = $options['user'];
	    $userid = $user->getId();
	    
	    /*
	    $builder->add('attachedFile', 'entity', array(
	            'class' => 'IMDCTerpTubeBundle:Media',
	            'property' => 'title',
	            'empty_value' => 'Choose an option',
	            'required' => false,
	            'label' => 'File',
	            'query_builder' => function(EntityRepository $er) use ($userid) {
	                return $er->createQueryBuilder('m')
	                            ->where('m.owner = :id')
	                            ->setParameter('id', $userid);
	            },
	    ));
	    */
	    
	    $builder->add('mediatextarea', 'text', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'Media ID',
                                                    ));
	    
	    $builder->add('content', null, array('label' => 'Content',
	    ));
	    
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'PostEditForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Post',));
		
		$resolver->setRequired(array(
		        'user',
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		));
	}
}