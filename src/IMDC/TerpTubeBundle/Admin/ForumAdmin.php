<?php
// src/IMDC/TerpTubeBundle/Admin/ForumAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Allows manipulation of Forum objects in the admin interface
 * 
 * @author paul
 *
 */
class ForumAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('titleText', 'text')
            ->add('titleMedia', 'sonata_type_collection', array('required' => false))
            ->add('creator', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('creationDate')
            ->add('forumAdmins', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User', 'required' => false, 'multiple' => true))
            ->add('forumModerators', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User', 'required' => false, 'multiple' => true))
            ->add('lastActivity')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('creator')
            ->add('creationDate')
            ->add('lastActivity')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('titleText')
            ->add('titleMedia')
            ->add('creator.username')
            ->add('creationDate')
            ->add('forumAdmins')
            ->add('forumModerators')
            ->add('lastActivity')
        ;
    }
}