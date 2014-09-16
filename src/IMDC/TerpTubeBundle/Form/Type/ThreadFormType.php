<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Form\EventListener\AddParentForumSubscriber;
use IMDC\TerpTubeBundle\Form\Type\PermissionsType;

class ThreadFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    // User type
	    $user = $options['user'];
	     
	    $userid = $user->getId();
	    
	    $builder->add('title', 'text', array(
            'label' => 'Text Title'
        ));
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
	    $builder->add('mediatextarea', 'text', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'File',
                                                    'attr' => array('cols' => 1,
                                                                    'rows' => 1)));
	    /*
	    $builder->add('mediaIncluded', 'entity', array(
	            'class' => 'IMDCTerpTubeBundle:Media',
	            'property' => 'title',
	            'empty_value' => 'Choose an option',
	            'required' => false,
	            'label' => 'Your media files',
	            'query_builder' => function(EntityRepository $er) use ($userid) {
	                return $er->createQueryBuilder('m')
	                            ->where('m.owner = :id')
	                            ->setParameter('id', $userid);
	            },
	    ));
	    */

	    $builder->add('content', 'textarea', array(
            'label' => 'Supplementary Content - a brief description of the Topic',
            'required' => false,
            'attr' => array(
                'class' => 'autosize')
	    ));
	    
	    $builder->add('permissions', new PermissionsType(array('user' => $user)));
	    
	    // this was used to test if you could choose a different parentForum from a selection drop down
	    //$builder->addEventSubscriber(new AddParentForumSubscriber());
	    
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ThreadForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Thread',));

		$resolver->setRequired(array(
		        'user',
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		));

		
	}
}