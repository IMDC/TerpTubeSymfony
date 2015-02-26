<?php
// src/IMDC/TerpTubeBundle/Form/EventListener/AddToFieldSubscriber.php
namespace IMDC\TerpTubeBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddToFieldSubscriber implements EventSubscriberInterface
{
    /**
    * 
    */
    public static function getSubscribedEvents() {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
     
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        
        if (!$data) {
            $form->add('to', null, array('label' => 'To (separate people with a space)', 
                                            'mapped' => false,
                                            'attr' => array('value' => 'Paul'),
            ));
        }
        else {
            $form->add('to', null, array('label' => 'To (separate people with a space)', 'mapped' => false));
        }
    } 
 
}
