<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

class ResourceFileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
//		parent::buildForm($builder, $options);
		$builder->add('file', 'file');
		
	}
	

	public function getName()
	{
		return 'imdc_terptube_resource_file';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',));
	}
}
?>