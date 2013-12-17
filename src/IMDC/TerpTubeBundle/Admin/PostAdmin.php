<?php
// src/IMDC/TerpTubeBundle/Admin/PostAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PostAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('author', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('content', 'text', array('label' => 'Post Content'))
            ->add('created', 'date', array('label' => 'Created At'))
            ->add('startTime', 'number')
            ->add('endTime', 'number')
            ->add('parentThread', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\Thread', 'property' => 'title'))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('author')
            ->add('content')
            ->add('created')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('author')
            ->add('content')
            ->add('created')
        ;
    }
}