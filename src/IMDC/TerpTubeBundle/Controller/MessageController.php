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
        $message = new Message();
/*         $message->setSubject("A test message");
        $message->setContent("This is some text that would go inside an email type message");
        $message->setSentDate(new \DateTime('now')); */
        
        $form = $this->createForm(new PrivateMessageType(), $message);
        
        $form->handleRequest($request);
        
        
        if ($form->isValid()) {
            // set sentDate of message to be when form request recieved
            // this is automatically set again when the object is inserted into the database anyway
            $message->setSentDate(new \DateTime('now'));
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            
            return new Response('Created message id '.$message->getId());
        }
        
        return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array(
                'form' => $form->createView(),
        ));
        
    }
    
    public function viewAllMessagesAction() 
    {
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            
            $user = $this->getUser();
            $messages = $this->getDoctrine()
                            ->getRepository('IMDCTerpTubeBundle:Message')
                            ->findBy(array('user' => $user->getId()));
            
            return $this->render('IMDCTerpTubeBundle:Message:viewall.html.twig',
                                  array('messages' => $messages)
                    );
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
}