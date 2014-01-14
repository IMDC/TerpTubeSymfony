<?php
// src/IMDC/TerpTubeBundle/Admin/UserAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->with('General')
            ->add('userName', 'text')
            ->add('email')
            ->add('plainPassword', 'text')
        ->end()        
        ->with('Profile')
            ->add('profile', 'sonata_type_admin')
        ->end()
        ->with('Groups')
        /*
            ->add('userGroups', 'entity', array('class' => 'IMDCTerpTubeBundle:UserGroup',
                                                'property' => 'name',
                                                'empty_value' => 'Select user group',
                                                'required' => false,
                                                'label' => 'User groups'
            ))
            */
            ->add('userGroups', null, array('required' => false))
//             ->add('roleGroups', 'sonata_type_collection')
        ->end()
        ->with('Friends')
            ->add('friendsList', null, array('required' => false))
        ->end()

        ->with('Management')
            ->add('roles')
            ->add('locked', null, array('required' => false))
            ->add('expired', null, array('required' => false))
            ->add('enabled', null, array('required' => false))
            ->add('credentialsExpired', null, array('required' => false))
        ->end()
            
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('roles')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('userName')
            ->add('profile.firstName')
            ->ADD('profile.lastName')
            ->add('joinDate')
            ->add('roles')
            ->add('userGroups')
            ->add('roleGroups')
        ;
    }
}