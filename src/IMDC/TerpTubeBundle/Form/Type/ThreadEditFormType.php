<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\Thread;

class ThreadEditFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    // User type
	    $user = $options['user'];
	    $userid = $user->getId();
	    
	    $thread = $options['thread'];
	    
	    /*
	    $builder->add('includedFile', 'entity', array(
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
	    
	    $builder->add('title', null, array('label' => 'Title',
	    ));
	    
		$builder->add('content', null, array('label' => 'Supplemental Text Description'));
		
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ThreadEditForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Thread',));
		
		$resolver->setRequired(array(
		        'user',
		        'thread',
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		        'thread' => 'IMDC\TerpTubeBundle\Entity\Thread',
		));
	}
}