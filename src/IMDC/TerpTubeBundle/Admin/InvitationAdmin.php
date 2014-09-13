<?php
// src/IMDC/TerpTubeBundle/Admin/InvitationAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Allows manipulation of Invitation objects in the admin interface
 * 
 * @author paul
 *
 */
class InvitationAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('creator')
            ->add('recipient')
            ->add('dateCreated')
            //->add('becomeMentee')
            //->add('becomeMentor')
            ->add('type')
            ->add('isAccepted')
            ->add('dateAccepted')
            ->add('isDeclined')
            ->add('dateDeclined')
            ->add('isCancelled')
            ->add('dateCancelled')

        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('creator')
            ->add('recipient')
            ->add('dateCreated')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('creator')
            ->addIdentifier('recipient')
            ->add('dateCreated')
            //->add('becomeMentee')
            //->add('becomeMentor')
            ->add('type')
            ->add('isAccepted')
            ->add('dateAccepted')
            ->add('isDeclined')
            ->add('dateDeclined')
            ->add('isCancelled')
            ->add('dateCancelled')
        ;
    }
}