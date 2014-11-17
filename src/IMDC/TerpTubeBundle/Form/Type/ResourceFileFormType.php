<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\Validator\Constraints\File;

class ResourceFileFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
//		parent::buildForm($builder, $options);
		$builder->add('file', 'file'/*, array(
            'constraints' => array(new File())
        )*/);
		
	}
	

	public function getName()
	{
		return 'MediaForm_resource_file';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',));
	}
}
?>