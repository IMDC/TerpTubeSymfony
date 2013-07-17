<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use IMDC\TerpTubeBundle\Entity\Message;


class MessageController extends Controller
{
    public function createMessageAction()
    {
        $message = new Message();
        $message->setSubject("A test message");
        $message->setContent("This is some text that would go inside an email type message");
        $message->setSentDate(new \DateTime('2 days ago'));
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();
        
        return new Response('Created message id '.$message->getId());
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
            $response = new RedirectResponse($this->generateUrl(
                                                        'imdc_terp_tube_homepage',
                                                        array('message'=>'Please log in'))
                                            );
            return $response;
        }
    }
}