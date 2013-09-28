<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ThreadFromMediaFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    
	    // User type
	    $user = $options['user'];
	    $userid = $user->getId();
	    
	    $mediafile   = $options['resource'];
	    $mediafileid = $mediafile->getId();
	    /*
	    $builder->add('mediatextarea', 'textarea', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'Attached File',
                                                    'attr' => array('cols' => 1,
                                                                    'rows' => 1)));
	    
	    */
	    
	    $builder->add('mediaIncluded', 'entity', array(
	            'class' => 'IMDCTerpTubeBundle:Media',
	            'property' => 'title',
	            //'empty_value' => 'Choose an option',
	            'required' => true,
	            'label' => 'File',
	            'query_builder' => function(EntityRepository $er) use ($userid, $mediafileid) {
	                return $er->createQueryBuilder('m')
	                            ->where('m.owner = :id')
	                            ->andWhere('m.id = :rid')
	                            ->setParameter('id', $userid)
	                            ->setParameter('rid', $mediafileid);
	            },
	    ));
	    $builder->add('title');
	    
	    $builder->add('content', null, array('label' => 'Supplementary Content',
	    ));
	    
		$builder->add('submit', 'submit');
	}	

	public function getName()
	{
		return 'ThreadFromMediaForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Thread',));
		
		$resolver->setRequired(array(
		        'user',
		        'resource'
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		        'resource' => 'IMDC\TerpTubeBundle\Entity\Media',
		));
	}
}