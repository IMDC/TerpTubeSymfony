<?php
// src/IMDC/TerpTubeBundle/Admin/MediaAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

use IMDC\TerpTubeBundle\Event\UploadEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class MediaAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('owner', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('title')
            ->add('type', 'choice', array('choices' => array('0' => 'Image', '1' => 'Video', '2' => 'Audio', '3' => 'Other')))
            ->add('resource', 'sonata_type_admin')
            ->add('metaData', 'sonata_type_admin')
        ;
    }

    public function prePersist($media)
    {
        $this->triggerEvent($media);
    }
    
    public function preUpdate($media)
    {
        $this->triggerEvent($media);
    }
    
    private function triggerEvent($media)
    {
        if ($media->getResource()->getFile()) {
            // trigger uploaded event?
            $dispatcher = new EventDispatcher();
            $event = new UploadEvent($media);
            $dispatcher->dispatch(UploadEvent::EVENT_UPLOAD);
//             $media->refreshUpdated();
        }
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
            ->addIdentifier('title')
            ->add('type')
            ->add('resource.path')
            ->add('metaData.size', null, array('label' => 'Size (bytes)'))
            ->add('metaData.timeUploaded', null, array('label' => 'Time Uploaded'))
        ;
    }
}