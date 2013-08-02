<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

class VideoMediaFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('resource', new VideoResourceFileFormType());
		$builder->add('type', 'hidden', array('data'=> Media::TYPE_VIDEO));
		
	}
	

	public function getName()
	{
		return 'imdc_terptube_video_media';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Media',));
	}
}
?>