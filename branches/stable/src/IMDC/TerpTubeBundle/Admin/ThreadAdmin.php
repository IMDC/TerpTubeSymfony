<?php
// src/IMDC/TerpTubeBundle/Admin/PostAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Permissions;

/**
 * Allows manipulation and display of Thread objects in the admin interface
 * 
 * @author paul
 *
 */
class ThreadAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
//             ->add('author', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('creator', 'sonata_type_model', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('title', 'text')
            ->add('content', 'text', array('label' => 'Post Content'))
            ->add('creationDate', 'date', array('label' => 'Created At'))
            ->add('mediaIncluded', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
                'admin_code' => 'sonata.admin.media'
            ))
            ->add('parentForum', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\Thread', 'property' => 'title'))
//             ->add('permissions', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\Permissions', 'property' => 'accessLevel'))
            ->add('permissions', 'sonata_type_model_list', array(), array(
                'admin_code' => 'sonata.admin.permissions',
                'class' => 'IMDC\TerpTubeBundle\Entity\Permissions'
            ))
            ->add('sticky', null, array('required' => false))
            ->add('locked', null, array('required' => false))
            ->add('tags', null, array('required' => false))
            ->add('type', 'choice', array('choices' => array(Media::TYPE_IMAGE => 'Image', 
                                                            Media::TYPE_VIDEO => 'Video', 
                                                            Media::TYPE_AUDIO => 'Audio', 
                                                            Media::TYPE_OTHER => 'Other'))
            )
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('creator')
            ->add('title')
            ->add('parentForum')
            ->add('creationDate')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('creator')
            ->add('permissions.accessLevel')
            ->addIdentifier('parentForum')
            ->addIdentifier('title')
            ->add('content')
            ->add('mediaIncluded')
            ->add('creationDate')
        ;
    }
}