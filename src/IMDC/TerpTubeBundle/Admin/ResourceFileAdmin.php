<?php
// src/IMDC/TerpTubeBundle/Admin/ResourceFileAdmin.php

namespace IMDC\TerpTubeBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Allows manipulation of Resource files in the admin interface
 * 
 * @author paul
 *
 */
class ResourceFileAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if ($this->hasParentFieldDescription()) { // this admin is embedded
            // $getter will be something like 'getlogoImage'
            $getter = 'get' . $this->getParentFieldDescription()->getFieldName();
            
            // get hold of the parent object
            $parent = $this->getParentFieldDescription()->getAdmin()->getSubject();
            if ($parent) {
                $resourceFile = $parent->$getter();
            }
            else {
                $resourceFile = NULL;
            }
        }
        else {
            $resourceFile = $this->getSubject();
        }
        
        // use $fileFieldOptions so we can add other options to the field
        $fileFieldOptions = array('required' => false);
        
        if ($resourceFile && ($webPath = $resourceFile->getWebPath())) {
            // get the container so the full path to the resourcefile can be set
            $container = $this->getConfigurationPool()->getContainer();
            $fullPath = $container->get('request')->getBasePath().'/'.$webPath;
            
            // add a 'help' option container the preview's resourcefile tag
            if ($resourceFile->getPath() === 'mp4') {
                $fileFieldOptions['help'] = '<video width="320" height="240" controls>
                                                <source src="'.$container->get('request')->getBasePath().'/'.$resourceFile->getWebPathWebm().'" type="video/webm">
                                                Your browser does not support the video tag.
                                            </video>';
            }
            else if ($resourceFile->getPath() === 'm4a') {
                $fileFieldOptions['help'] = '<audio controls>
                                                <source src="'.$container->get('request')->getBasePath().'/'.$resourceFile->getWebPath().'" type="audio/mpeg">
                                                Your browser does not support the audio tag.
                                            </audio>';
            }
            else {
                $fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
            }
        }
        
        $formMapper
            ->add('file', 'file', array('required' => false), $fileFieldOptions)
//             ->add('webmExtension', 'choice', array('choices' => array('webm' => 'WebM'), 'required' => false))
        ;
    }
    
    public function prePersist($resourceFile) 
    {
        $this->manageFileUpload($resourceFile);
    }
    
    public function preUpdate($resourceFile) 
    {
        $this->manageFileUpload($resourceFile);
    }
    
    private function manageFileUpload($resourceFile) 
    {
        if ($resourceFile->getFile()) {

            $resourceFile->refreshUpdated();
        }
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('path')
            ->add('webmExtension')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('path')
            ->add('webmExtension')
        ;
    }
}