<?php
// src/IMDCTerpTubeBundle/Form/EventListener/AddParentForumSubscriber.php
namespace IMDC\TerpTubeBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This event subscriber checks if the Thread that will be created has been
 * assigned a parent forum. If the thread HAS a parent forum, a drop down
 * selection is added to the form and pre-selected with the forum's title.
 * 
 * This is currently not used but could be useful in the future.
 *
 * @author paul
 *
 */
class AddParentForumSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
    
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        
        if ($data->getParentForum()) {
//             $form->add('parentForum', 'text');
            $form->add('parentForum', 'entity', array(
                'class' => 'IMDCTerpTubeBundle:Forum',
                'property' => 'titleText',
                'empty_value' => 'Choose a forum',
                'required' => false,
                'label' => 'Forum',
            ));
        }
    }
}