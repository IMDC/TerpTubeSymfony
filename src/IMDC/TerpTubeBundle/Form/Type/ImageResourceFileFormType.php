<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Validator\Constraints\Image;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

/**
 * @deprecated
 */
class ImageResourceFileFormType extends AbstractType //TODO delete
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$imageConstraint = new Image();
		$imageConstraint->mimeTypes = array('image/jpeg', 'image/gif', 'image/png');
		$imageConstraint->maxSize = '4M';
		$builder->add('file', 'file', array('constraints'=>array($imageConstraint), 'attr'=>array('accept'=>'image/jpeg,image/gif,image/png')));
		
	}
	

	public function getName()
	{
		return 'MediaForm_image_resource_file';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',));
	}
}
?>