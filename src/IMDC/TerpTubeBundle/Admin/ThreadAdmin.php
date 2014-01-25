<?php
// src/IMDC/TerpTubeBundle/Admin/PostAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ThreadAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
//             ->add('author', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('creator', 'sonata_type_model_list', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('title')
            ->add('content', 'text', array('label' => 'Post Content'))
            ->add('creationDate', 'date', array('label' => 'Created At'))
            ->add('mediaIncluded', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position'
            ))
            ->add('parentForum', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\Thread', 'property' => 'title'))
            ->add('permissions', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\Permissions', 'property' => 'accessLevel'))
            ->add('sticky')
            ->add('locked')
            ->add('tags')
            ->add('type')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('creator')
            ->add('title')
            ->add('creationDate')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('creator')
            ->add('permissions.accessLevel')
            ->add('title')
            ->add('content')
            ->add('mediaIncluded')
            ->add('creationDate')
        ;
    }
}