<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

class ImageMediaFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
//		parent::buildForm($builder, $options);
//FIXME look into file validation for subentities
		$builder->add('resource', new ResourceFileFormType());
		$builder->add('type', 'hidden', array('data'=> Media::TYPE_IMAGE));
		
	}
	

	public function getName()
	{
		return 'imdc_terptube_image_media';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Media',));
	}
}
?>