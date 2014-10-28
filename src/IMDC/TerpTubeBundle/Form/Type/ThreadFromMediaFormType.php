<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ThreadFromMediaFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    //TODO FIXME
	    // User type
	    $user = $options['user'];
	    $userid = $user->getId();
	    
	    // media info
	    $mediafile   = $options['resource'];
	    $mediafileid = $mediafile->getId();
	    
	    // entity manager
	    $em = $options['em'];
	    
	    /*
	    $builder->add('mediatextarea', 'textarea', array('required' => false, 
	                                                'mapped' => false,
	                                                'read_only' => true,
	                                                'label' => 'Attached File',
                                                    'attr' => array('cols' => 1,
                                                                    'rows' => 1)));
	    
	    */
	    
	    /**
	     * Replace this with the below skeleton when Forums have permissions
	     */
	    $builder->add('parentForum', 'entity', array(
	            'class' => 'IMDCTerpTubeBundle:Forum',
	            'property' => 'titleText',
	            'required' => true,
	            'label' => 'Which forum would you like to post this under?'
	    ));
	    
	    /***SKELETON AND NOT USED, IMPLEMENT WHEN FORUMS HAVE PERMISSIONS***********/
// 	    $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->findForumsUserHasAccessTo($user);
// 	    $builder->add('parentForum', 'entity', array(
// 	            'class' => 'IMDCTerpTubeBundle:Forum',
// 	            'property' => 'title',
// 	            'required' => true,
// 	            'label' => 'Which forum would you like to post this under?',
// 	            'choices' => $forums,
// 	    ));
	    
	    // retrieve the media entity the user has decided to post
	    // can we use the repositor to retrieve the file rather than the query builder?
	    $mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')->findOneBy(
	    	array('owner' => $userid, 'id' => $mediafileid)
	    );
	    $builder->add('mediaIncluded', 'entity', array(
	            'class' => 'IMDCTerpTubeBundle:Media',
	            'property' => 'title',
	            'disabled' => true,
	            'required' => true,
	            'label' => 'File',
// 	            'choices' => $mediaFile,
	            'query_builder' => function(EntityRepository $er) use ($userid, $mediafileid) {
	                return $er->createQueryBuilder('m')
	                            ->where('m.owner = :id')
	                            ->andWhere('m.id = :rid')
	                            ->setParameter('id', $userid)
	                            ->setParameter('rid', $mediafileid);
	            },
            'attr' => array(
                'style' => 'display: none;'
            )
	    ));
	    
	    
	    $builder->add('title');
	    
	    $builder->add('content', null, array('label' => 'Supplementary Content',));
	    
	    $builder->add('permissions', new PermissionsType(array('user' => $user)));
	    
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
		        'resource',
		        'em',
        ));
		$resolver->setAllowedTypes(array(
		        'user' => 'IMDC\TerpTubeBundle\Entity\User',
		        'resource' => 'IMDC\TerpTubeBundle\Entity\Media',
		        'em' => 'Doctrine\Common\Persistence\ObjectManager',
		));
	}
}