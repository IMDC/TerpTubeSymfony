<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $canTemporal = $options['canTemporal'];

	    $builder->add('mediatextarea', 'hidden', array(
            'mapped' => false
        ));

        if ($canTemporal) {
            $builder->add('startTime', 'number', array(
                'required' => false,
                'precision' => 2,
                'attr' => array(
                    'style' => 'display: none;')
            ));

            $builder->add('endTime', 'number', array(
                'required' => false,
                'precision' => 2,
                'attr' => array(
                    'style' => 'display: none;')
            ));
        }
	    
		$builder->add('content', 'textarea', array(
            'label' => false,
            'required' => false,
            'attr' => array(
                'class' => 'autosize',
                'placeholder' => 'Write a comment...')
        ));
	}

	public function getName()
	{
		return 'PostForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Post'
        ));
		
		$resolver->setRequired(array(
            'canTemporal'
        ));
	}
}
