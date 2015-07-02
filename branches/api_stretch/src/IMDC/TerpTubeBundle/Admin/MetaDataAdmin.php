<?php
// src/IMDC/TerpTubeBundle/Admin/MetaDataAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Allows manipulation of MetaData objects in the admin interface
 * 
 * @author paul
 *
 */
class MetaDataAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', null, array('disabled' => true))
            ->add('duration')
            ->add('height')
            ->add('size')
            ->add('timeUploaded')
            ->add('width')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('timeUploaded')
            ->add('duration')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('duration')
            ->add('height')
            ->add('width')
            ->add('size')
            ->add('timeUploaded')
        ;
    }
}