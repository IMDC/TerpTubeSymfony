<?php
// src/IMDC/TerpTubeBundle/Admin/UserGroupAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserGroupAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('dateCreated')
            ->add('userFounder')
            ->add('members')
            ->add('moderators')
            ->add('joinByInvitationOnly')
            ->add('openForNewMembers')
            ->add('visibleToPublic')
            ->add('visibleToRegisteredUsers')
        /*
            ->add('userGroups', 'entity', array('class' => 'IMDCTerpTubeBundle:UserGroup',
                                                'property' => 'name',
                                                'empty_value' => 'Select user group',
                                                'required' => false,
                                                'label' => 'User groups'
            ))
            
            ->add('userGroups', 'sonata_type_collection', array('required' => false))
            ->add('roleGroups', 'entity', array('class' => 'IMDCTerpTubeBundle:RoleGroup', 
                                                'property' => 'name',
                                                'empty_value' => 'Select role group',
                                                'required' => false,
                                                'label' => 'Role groups'
            ))
            ->add('friendsList', 'sonata_type_collection', array('required' => false))
            ->add('roles')  
            ->add('locked', null, array('required' => false))
            ->add('expired', null, array('required' => false))
            ->add('enabled', null, array('required' => false))
            ->add('credentialsExpired', null, array('required' => false))
            */
            
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('dateCreated')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('dateCreated')
            ->ADD('members')
            ->add('moderators')
            ->add('admins')
            ->add('joinByInvitationOnly')
            ->add('openForNewMembers')
            ->add('visibleToPublic')
            ->add('visibleToRegisteredUsers')
        ;
    }
}