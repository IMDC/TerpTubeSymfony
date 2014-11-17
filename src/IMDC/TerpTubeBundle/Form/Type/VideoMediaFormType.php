<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

/**
 * @deprecated
 */
class VideoMediaFormType extends AbstractMediaFormType //TODO delete
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		$builder->add('resource', new VideoResourceFileFormType());
		$builder->add('type', 'hidden', array('data'=> Media::TYPE_VIDEO));
		
	}
	

	public function getName()
	{
		return 'MediaForm_video';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Media',));
	}
}
?>