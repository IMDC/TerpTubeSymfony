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

            $em = $this->getDoctrine()->getManager();
            
            $user = $this->getUser();
            $user->addSentMessage($message);
            foreach ($message->getRecipients() as $recp) {
                $recp->addReceivedMessage($message);
                // request persistence of user object to database
                $em->persist($recp);
            }
            
            // request to persist message object to database
            $em->persist($message);
            
            // persist all objects to database
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
    
    public function viewAllPrivateMessagesAction($feedbackmsg=NULL) 
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
        /**
        $messages = $this->getDoctrine()
                        ->getRepository('IMDCTerpTubeBundle:Message')
                        ->findBy(array('owner' => $user->getId()));
        **/
        
        $em = $this->getDoctrine()->getManager();
        
        /* $messages = $em->getRepository('IMDCTerpTubeBundle:Message')
                            ->findAllReceivedInboxMessagesForUser($user, 30); */
        
        $messages = $user->getReceivedMessages();
        
        // loop through the messages and temporarily mark them as read or unread
        foreach ($messages as $msg) {
            if ($msg->isMessageRead($user)) {
                $msg->setTempRead(TRUE);
            }
            else {
                $msg->setTempRead(FALSE);
            }
        }
        
        // if no feedback message, just show all messages
        if (is_null($feedbackmsg)) {
            $response = $this->render('IMDCTerpTubeBundle:Message:inbox.html.twig',
                                  array('messages' => $messages)
                        );
            return $response;
        }
        // show all messages and the feedback message
        else {
            $response = $this->render('IMDCTerpTubeBundle:Message:inbox.html.twig',
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
        
        $messages = $user->getReceivedMessages();

        // make sure the message exists
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository('IMDCTerpTubeBundle:Message')
                      ->findOneById($messageid);
        
        if (!$messages->contains($message)) {
            throw $this->createNotFoundException(
                    'No message found for id: '.$messageid
            );
        }
        // make sure the current user is a recipient on this message
        else if (!$message->getRecipients()->contains($user)) {
            throw $this->createNotFoundException(
                    'User is not found in the recipients for this message id: '.$messageid
            );
        }
        
/*         $em->getRepository('IMDCTerpTubeBundle:Message')
            ->deleteMessageFromInbox($messageid, $this->getUser()->getId()); */

        // remove message from users received messages
        $user->removeReceivedMessage($message);
        
        // add message to users deleted messages
        $user->addDeletedMessage($message);
        
        $em->persist($user);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add(
                'inbox',
                'Message id: ' .$message->getId() . ' deleted'
        );
        return $this->redirect($this->generateUrl('imdc_message_view_all'));

    }
    
    public function archiveMessageAction($messageid)
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
        
        $messages = $user->getReceivedMessages();
        
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository('IMDCTerpTubeBundle:Message')
        ->findOneById($messageid);
        
        if (!$messages->contains($message)) {
            throw $this->createNotFoundException(
                    'No message found for id: '.$messageid
            );
        }
        
        /*         $em->getRepository('IMDCTerpTubeBundle:Message')
         ->deleteMessageFromInbox($messageid, $this->getUser()->getId()); */
        
        //$message->addUsersArchived($user);
        $user->addArchivedMessage($message);
       
        //$em->persist($message);
        $em->persist($user);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add(
                'inbox',
                'Message id: ' .$message->getId() . ' archived'
        );
        return $this->redirect($this->generateUrl('imdc_message_view_all'));
    }
    
    public function viewSentMessagesAction() 
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
        /**
         $messages = $this->getDoctrine()
         ->getRepository('IMDCTerpTubeBundle:Message')
         ->findBy(array('owner' => $user->getId()));
        **/
        
        //$em = $this->getDoctrine()->getManager();
        /* $messages = $em->getRepository('IMDCTerpTubeBundle:Message')
                        ->findAllSentMessagesForUser($user); */
        
        $messages = $user->getSentMessages();
        
        // if no feedback message, just show all messages
        if (is_null($feedbackmsg)) {
            $response = $this->render('IMDCTerpTubeBundle:Message:sentmessages.html.twig',
                    array('messages' => $messages)
            );
            return $response;
        }
        // show all messages and the feedback message
        else {
            $response = $this->render('IMDCTerpTubeBundle:Message:sentmessages.html.twig',
                    array('messages' => $messages,
                          'feedback' => $feedbackmsg)
            );
            return $response;
        }
    }
    
    public function viewArchivedMessagesAction()
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
        
        // todo: implement message archive system
        $messages = $user->getArchivedMessages();
        
        $response = $this->render('IMDCTerpTubeBundle:Message:archivedmessages.html.twig',
                array('messages' => $messages)
        );
        return $response;
    }
    
    public function viewMessageAction($messageid) 
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
        
        $message = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message')->findOneBy(array('id' => $messageid));
        
        //TODO: fix this?!
        $alreadyRead = $message->isMessageRead($user);
        
        // if the message is already read, don't try to insert it again as this causes SQL errors
        if ($alreadyRead) {
            // skip
        }
        else {
            $user->addReadMessage($message);
            //$message->addUsersRead($user);
            // flush object to database
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }
        
        $response = $this->render('IMDCTerpTubeBundle:Message:viewprivatemessage.html.twig',
                array('message' => $message)
        );
        return $response;

    }
    
    public function isMessageReadAction($messageid)
    {
        $user = $this->getUser();
        $request = $this->get('request');
        $mid = $request->request->get('msgId');
        
        $repository = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message');
        $message = $repository->findOneById($mid);
        
        if ($message->isMessageRead($user)) {
            $return = array('responseCode' => 200, 'feedback' => 'Message marked as read');
        }
        else {
            $return = array('responseCode' => 400, 'feedback'=> 'Error marking message as read');
        }
        
        $return = json_encode($return); // json encode the array
        return new Response($return, 200, array('Content-Type'=>'application/json')); //make sure it has the correct content type
        
    }
    
    public function recentMessagesAction($max = 30)
    {
        
    }
}