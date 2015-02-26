<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use IMDC\TerpTubeBundle\Entity\Media;

class LanguageFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name',null, array('required'=>true, 'label' => 'form.profile.language.name', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('type', 'choice', array('required'=>true, 'choices'=>array(0 => 'form.profile.language.type.0', 1 => 'form.profile.language.type.1'), 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.language.type.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('proficiency', 'choice', array('required'=>true, 'choices'=>array(1 => 'form.profile.language.proficiency.1', 2 => 'form.profile.language.proficiency.2', 3 => 'form.profile.language.proficiency.3', 4 => 'form.profile.language.proficiency.4', 5 => 'form.profile.language.proficiency.5'), 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.language.proficiency.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
	}
	

	public function getName()
	{
		return 'imdc_terptube_language';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\Language',));
	}
}
?>