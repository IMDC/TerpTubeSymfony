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
        $isPostReply = $options['is_post_reply'];

        $builder->add('attachedFile', 'media_chooser', array(
            'section' => $isPostReply ? 'post_reply' : 'post',
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
                'placeholder' => 'Write a reply...')
        ));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'is_post_reply' => false,
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Post'))
            ->setRequired(array(
                'canTemporal'));
    }

	public function getName()
	{
		return 'post';
	}
}
