<?php
// src/IMDC/TerpTubeBundle/Admin/MessageAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class to allow manipulation of Message objects in the admin interface
 * 
 * @author paul
 *
 */
class MessageAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('owner', 'sonata_type_model_list', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('recipients')
            ->add('subject')
            ->add('content', 'text', array('label' => 'Message Content'))
            ->add('attachedMedia', 'sonata_type_collection', array('property_path' => 'attachedMedia'))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('owner')
            ->add('subject')
            ->add('sentDate')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('owner')
            ->add('recipients')
            ->addIdentifier('subject')
            ->add('sentDate')
            ->addIdentifier('content')
        ;
    }
}