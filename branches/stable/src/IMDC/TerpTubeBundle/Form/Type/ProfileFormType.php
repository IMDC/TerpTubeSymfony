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
		$builder->add('middleName', null, array('required'=>false, 'label' => 'form.profile.middleName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('lastName', null, array('label' => 'form.profile.lastName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('textBio', 'textarea', array('required'=>false, 'label' => 'form.profile.textBio', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('birthDate', 'birthday', array('invalid_message'=>'form.profile.birthDate.invalid','required'=>false, 'label' => 'form.profile.birthDate.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('gender', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array('m' => 'form.profile.gender.m', 'f' => 'form.profile.gender.f'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.gender.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('city', null, array('label' => 'form.profile.city', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('country', 'country', array('label' => 'form.profile.country', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('skypeName', null, array('required'=>false, 'label' => 'form.profile.skypeName', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('languages', 'collection', array('required'=>false, 'type' => new LanguageFormType(), 'allow_add'=> true, 'allow_delete'=>true, 'options' => array('required'=>true, 'label' => ' ', 'translation_domain' => 'IMDCTerpTubeBundle')));
		$builder->add('interestedInMentoredByMentor', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array(true => 'form.generic.yes', false => 'form.generic.no'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.interestedInMentoredByMentor.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('interestedInMentoredByInterpreter', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array(true => 'form.generic.yes', false => 'form.generic.no'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.interestedInMentoredByInterpreter.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('interestedInMentoringSignLanguage', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array(true => 'form.generic.yes', false => 'form.generic.no'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.interestedInMentoringSignLanguage.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('interestedInMentoringInterpreter', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array(true => 'form.generic.yes', false => 'form.generic.no'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.interestedInMentoredingInterpreter.title', 'translation_domain' => 'IMDCTerpTubeBundle'));
		$builder->add('interestedInMentoringMentor', 'choice', array('empty_value' => 'form.generic.empty', 'choices'=>array(true => 'form.generic.yes', false => 'form.generic.no'), 'required'=>false, 'expanded' => true, 'multiple' => false, 'label' => 'form.profile.interestedInMentoringMentor.title', 'translation_domain' => 'IMDCTerpTubeBundle'));

        $builder->add('profileVisibleToPublic', 'checkbox', array(
            'label' => 'form.profile.profileVisibleToPublic.title',
            'required' => false
        ));
	}
	

	public function getName()
	{
		return 'imdc_terptube_user_profile';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\UserProfile',
            'translation_domain' => 'IMDCTerpTubeBundle'
        ));
	}
}
?>