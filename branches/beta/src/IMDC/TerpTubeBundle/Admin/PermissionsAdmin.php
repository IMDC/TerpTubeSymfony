<?php
// src/IMDC/TerpTubeBundle/Admin/PermissionsAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Allows manipulation of Permission objects in the admin interface.
 * 
 * @author paul
 *
 */
class PermissionsAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
//             ->add('author', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('id', null, array('disabled' => true))
            ->add('accessLevel')
            ->add('usersWithAccess')
            ->add('userGroupsWithAccess')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('accessLevel')
            ->add('usersWithAccess')
            ->add('userGroupsWithAccess')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('accessLevel')
            ->add('usersWithAccess')
            ->add('userGroupsWithAccess')
        ;
    }
}