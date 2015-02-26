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

        $builder->add('attachedFile', 'media_chooser', array(
            'label' => false
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
                'style' => 'height: 35px;', //FIXME css me
                'placeholder' => 'Write a reply...')
        ));
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

	public function getName()
	{
		return 'post';
	}
}
