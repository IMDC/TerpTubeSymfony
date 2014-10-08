<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use IMDC\TerpTubeBundle\Form\EventListener\AddParentForumSubscriber;

class ThreadFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        if ($options['canChooseMedia']) {
            $builder->add('mediatextarea', 'media');
        }
	    
	    $builder->add('title', 'text', array(
            'label' => 'Text Title'
        ));

	    $builder->add('content', 'textarea', array(
            'label' => 'Supplemental Text Description',
            'required' => false,
            'attr' => array(
                'class' => 'autosize')
	    ));

        $builder->add('accessType', 'access_type', array(
            'class' => 'IMDC\TerpTubeBundle\Entity\Thread'
        ));
	    
	    // this was used to test if you could choose a different parentForum from a selection drop down
	    //$builder->addEventSubscriber(new AddParentForumSubscriber());
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'canChooseMedia' => true,
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Thread'
        ));
    }

	public function getName()
	{
		return 'ThreadForm';
	}
}
