<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

class AudioResourceFileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$audioConstraint = new File();
		$audioConstraint->mimeTypes = array('audio/*');
		$builder->add('file', 'file', array('constraints'=>array($audioConstraint), 'attr'=>array('accept'=>'audio/*')));
		
	}
	

	public function getName()
	{
		return 'MediaForm_audio_resource_file';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',));
	}
}
?>