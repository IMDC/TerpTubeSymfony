<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

/**
 * @deprecated
 */
class VideoResourceFileFormType extends AbstractType //TODO delete
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$videoConstraint = new File();
		$videoConstraint->mimeTypes = array('video/*', 'application/octet-stream');
		$builder->add('file', 'file', array('constraints'=>array($videoConstraint), 'attr'=>array('accept'=>'video/*')));
		//https://github.com/alchemy-fr/PHP-FFmpeg look into this after the file is uploaded
	}
	

	public function getName()
	{
		return 'MediaForm_video_resource_file';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',));
	}
}
?>