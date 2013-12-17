<?php
// src/IMDC/TerpTubeBundle/Admin/MediaAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class MediaAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('owner', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('title')
            ->add('type')
            ->add('resource', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\ResourceFile',
                                                'property' => 'name',
            ))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('owner')
            ->add('title')
            ->add('type')
            ->add('isReady')    
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('owner')
            ->add('title')
            ->add('type')
            ->add('metaData.size')
            ->add('metaData.timeUploaded')
        ;
    }
}