<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends BaseType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
//		parent::buildForm($builder, $options);
		$builder->add('firstName', null, array('label' => 'form.profile.firstName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('middleName', null, array('label' => 'form.profile.middleName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('lastName', null, array('label' => 'form.profile.lastName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('birthDate', 'date', array('years' => range(date('Y'), date('Y') - 110),'label' => 'form.profile.birthDate', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('city', null, array('label' => 'form.profile.city', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('country', 'country', array('label' => 'form.profile.country', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('gender', 'choice', array('choices'=>array('m' => 'form.profile.gender.m', 'f' => 'form.profile.gender.f'), 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.gender.title', 'translation_domain' => 'IMDCTerpTubeBundle'));

	}
	
	public function buildUserForm($builder, $options)
	{
		parent::buildUserForm($builder, $options);
		$builder->add('profile',new MyProfileFormType(),array(
				'label' => 'PROFILE'
		));
	}

	public function getName()
	{
		return 'imdc_terptube_user_profile';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'IMDC\TerpTubeBundle\Entity\UserProfile',));
	}
}
?>