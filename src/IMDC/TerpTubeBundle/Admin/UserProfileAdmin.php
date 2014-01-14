<?php
// src/IMDC/TerpTubeBundle/Admin/UserProfileAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserProfileAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        
        $formMapper
        ->add('firstName')
        ->add('middleName', null, array('required' => false))
        ->add('lastName')
        ->add('gender', 'choice', array('choices' => array('m' => 'Male', 'f' => 'Female'), 'required' => false))
        ->add('profileVisibleToPublic')
        ->add('interestedInMentoredByInterpreter')
        ->add('interestedInMentoredByMentor')
        ->add('interestedInMentoringInterpreter')
        ->add('interestedInMentoringMentor')
        ->add('interestedInMentoringSignLanguage')
        ->add('city')
        ->add('country')
        ->add('languages', null, array('required' => false))
        ->add('skypeName', null, array('required' => false))
        ->add('textBio')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('firstName')
            ->add('middleName')
            ->add('lastName')
            ->add('gender')
            ->add('interestedInMentoredByInterpreter')
            ->add('interestedInMentoredByMentor')
            ->add('interestedInMentoringInterpreter')
            ->add('interestedInMentoringMentor')
            ->add('interestedInMentoringSignLanguage')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('firstName')
            ->add('middleName')
            ->add('lastName')
            ->add('gender')
            ->add('city')
            ->add('country')
            ->add('profileVisibleToPublic')
            ->add('skypeName')
        ;
    }
}