<?php
// src/IMDC/TerpTubeBundle/Admin/MediaAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Entity\Media;

class MediaAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('owner', 'entity', array('class' => 'IMDC\TerpTubeBundle\Entity\User'))
            ->add('title')
            ->add('type', 'choice', array('choices' => array(Media::TYPE_IMAGE => 'Image', Media::TYPE_VIDEO => 'Video', Media::TYPE_AUDIO => 'Audio', Media::TYPE_OTHER => 'Other')))
            ->add('resource', 'sonata_type_admin')
            ->add('metaData', 'sonata_type_admin')
        ;
    }

    /**
     * After persistence layer (Doctrine) is called, this event is fired
     * for CREATION of NEW objects 
     * 
     * @param IMDCTerpTubeBundle:Media $media
     */
    public function postPersist($media)
    {
        $this->triggerEvent($media);
    }
    
    /**
     * After persistence layer (Doctrine) is called, this event is fired
     * for EDITING of EXISTING objects
     * 
     * @param IMDCTerpTubeBundle:Media $media
     */
    public function postUpdate($media)
    {
        // reset media ready status so media is re-encoded
        // todo: fix this so that media is only re-encoded if resource file is changed
        $media->setIsReady(Media::READY_NO);
        $this->triggerEvent($media);
    }
    
    /**
     * Using the event dispatcher service, this method dispatches an
     * UploadEvent to trigger media transcoding and metadata generation
     * for the given media object
     * 
     * @param IMDCTerpTubeBundle:Media $media
     */
    private function triggerEvent($media)
    {
        // get event dispatcher service from the container
        $ed = $this->getConfigurationPool()->getContainer()->get('event_dispatcher');
        $event = new UploadEvent($media);
        
        // dispatch upload event to trigger video transcoding and metadata generation
        $ed->dispatch(UploadEvent::EVENT_UPLOAD, $event);
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
            ->addIdentifier('id')
            ->addIdentifier('title')
            ->add('type')
            ->add('resource.path')
            ->add('isReady')
            ->add('metaData.size', null, array('label' => 'Size (bytes)'))
            ->add('metaData.timeUploaded', null, array('label' => 'Time Uploaded'))
        ;
    }
}