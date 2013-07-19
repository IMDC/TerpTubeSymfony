<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;


use IMDC\TerpTubeBundle\Entity\Message;
use IMDC\TerpTubeBundle\Form\Type\PrivateMessageType;


class MessageController extends Controller
{
    public function createMessageAction(Request $request)
    {
        // check if user logged in
        $securityContext = $this->container->get('security.context');
        if(! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in first'
                    );
            return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
        }
        
        $message = new Message();
        /*
        $message->setSubject("A test message");
        $message->setContent("This is some text that would go inside an email type message");
        $message->setSentDate(new \DateTime('now')); 
        */
        
        $form = $this->createForm(new PrivateMessageType(), $message);
        
        $form->handleRequest($request);
        
        
        if ($form->isValid()) {
            
            // set sentDate of message to be when form request recieved
            // this is automatically set again when the object is inserted into the database anyway
            $message->setSentDate(new \DateTime('now'));
            
            // set owner/author of the message to be the currently logged in user
            $message->setOwner($this->getUser());

            // flush object to database
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add(
                    'inbox',
                    'Message sent successfully!'
            );
            return $this->redirect($this->generateUrl('imdc_message_view_all'));
            
            //return new Response('Created message id '.$message->getId());
        }
        
        // form not valid, show the basic form
        return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array(
                'form' => $form->createView(),
        ));
        
    }
    
    public function viewAllMessagesAction($feedbackmsg=NULL) 
    {
        // check if user logged in
        $securityContext = $this->container->get('security.context');
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in first'
            );
            return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
        }
        
        $user = $this->getUser();
        $messages = $this->getDoctrine()
                        ->getRepository('IMDCTerpTubeBundle:Message')
                        ->findBy(array('owner' => $user->getId()));
        
        // if no feedback message, just show all messages
        if (is_null($feedbackmsg)) {
            $response = $this->render('IMDCTerpTubeBundle:Message:viewall.html.twig',
                                  array('messages' => $messages)
                        );
            return $response;
        }
        // show all messages and the feedback message
        else {
            $response = $this->render('IMDCTerpTubeBundle:Message:viewall.html.twig',
                                  array('messages' => $messages,
                                        'feedback' => $feedbackmsg)
                        );
            return $response;
        }
    }
    
    public function messageSuccessAction() 
    {
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            //return $this->render('IMDCTerpTubeBundle:Message:messagesent.html.twig',
              //                      array('info' => 'Your message has been sent.'));
              
            $response = $this->forward('IMDCTerpTubeBundle:Message:viewAllMessages',
                                        'Your message has been sent succesfully');
        }
        else
        {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in first'
            );
            return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
        }
    }
    
    public function deleteMessageAction($messageid)
    {
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $em = $this->getDoctrine()->getManager();
            $message = $em->getRepository('IMDCTerpTubeBundle:Message')
                          ->findOneById($messageid);
            
            if (!$message) {
                throw $this->createNotFoundException(
                        'No message found for id: '.$id
                        );
            }
            
            $em->getRepository('IMDCTerpTubeBundle:Message')
                ->deleteMessageFromInbox($messageid, $this->getUser()->getId());
            
            $this->get('session')->getFlashBag()->add(
                    'inbox',
                    'Message id: ' .$message->getId() . ' deleted'
            );
            return $this->redirect($this->generateUrl('imdc_message_view_all'));
        }
        else {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You do not have access to this resource.'
            );
            return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
        }
    }
}