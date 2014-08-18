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

use FOS\UserBundle\Model\UserManager;
use IMDC\TerpTubeBundle\Entity\Message;
use IMDC\TerpTubeBundle\Form\Type\PrivateMessageType;
use IMDC\TerpTubeBundle\Form\Type\PrivateMessageReplyType;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Controller for the all Private Message (similar to email) actions 
 * @author paul
 * 
 */
class MessageController extends Controller
{
    /**
     * Create a new private message
     * 
     * @param Request $request
     * @param string $userid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
	public function createMessageAction(Request $request, $userid = null)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		
		$message = new Message();
	
		$form = $this->createForm(new PrivateMessageType(), $message, array(
		        'em' => $em,
		        'user' => $user,
		));
		
		$form->handleRequest($request);

		if ($form->isValid())
		{

			// set sentDate of message to be when form request received
			// this is automatically set again when the object is inserted into the database anyway
			$message->setSentDate(new \DateTime('now'));

			// set owner/author of the message to be the currently logged in user
			$message->setOwner($this->getUser());

			$user = $this->getUser();
			$user->addSentMessage($message);
			$em->persist($user);
			
			foreach ($message->getRecipients() as $recipient) {
			    $recipient->addReceivedMessage($message);
			    $em->persist($recipient);
			}

			// request to persist message object to database
			$em->persist($message);

			// persist all objects to database
			$em->flush();

			$this->get('session')->getFlashBag()->add('success', 'Message sent successfully!');
			return $this->redirect($this->generateUrl('imdc_message_view_all'));

		}

		// form not valid, show the basic form
		//return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Message:new.html.twig', array(
            'form' => $form->createView()
        ));
	}

	/**
	 * Create a new private message to another specific user
	 * NOT SURE IF THIS IS USED ANYWHERE
	 * 
	 * @param Request $request
	 * @param string $username
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function createMessageToAction(Request $request, $username)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		$em = $this->getDoctrine()->getManager();
		
		$message = new Message();
		$user = $this->getUser();

		//$form = $this->createForm(new PrivateMessageType(), $message);

		$form = $this->createForm(new PrivateMessageType(), $message, array(
		    'em' => $em,
		    'user' => $user,
		));
		
		$form->handleRequest($request);

		if ($form->isValid())
		{
			// set sentDate of message to be when form request received
			// this is automatically set again when the object is inserted into the database anyway
			$message->setSentDate(new \DateTime('now'));

			// set owner/author of the message to be the currently logged in user
			$message->setOwner($this->getUser());

			$user->addSentMessage($message);
			$em->persist($user);

			$existingUsers = new \Doctrine\Common\Collections\ArrayCollection();
			$userManager = $this->container->get('fos_user.user_manager');

			// split up the recipients by whitespace
			$rawrecips = explode(' ', $form->get('to')->getData());
			foreach ($rawrecips as $possuser) {
				try {
					$theuser = $userManager->loadUserByUsername($possuser);
					$existingUsers[] = $theuser;
					$message->addRecipient($theuser);
				}
				catch (UsernameNotFoundException $e) {
					//FIXME: create message to user about recip not found
				}
			}

			foreach ($existingUsers as $euser) {
				$euser->addReceivedMessage($message);
				$em->persist($euser);
			}

			// request to persist message object to database
			$em->persist($message);

			// persist all objects to database
			$em->flush();

			$this->get('session')->getFlashBag()->add('inbox', 'Message sent successfully!');
			return $this->redirect($this->generateUrl('imdc_message_view_all'));
		}

		// form not valid, show the basic form
		return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array('form' => $form->createView(),));

	}

	public function viewAllPrivateMessagesAction(Request $request, $feedbackmsg = NULL)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$em = $this->getDoctrine()->getManager();

// 		$messages = $em->getRepository('IMDCTerpTubeBundle:User')->getMostRecentInboxMessages($user, 30);
		$messages = $user->getReceivedMessages();

        //return $this->render('IMDCTerpTubeBundle:Message:inbox.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Message:index.html.twig', array(
            'messages' => $messages,
            'feedback' => $feedbackmsg,
            'tab' => 'inbox'
        ));
	}

	public function messageSuccessAction(Request $request)
	{
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) 
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		else 
		{
			$response = $this
					->forward('IMDCTerpTubeBundle:Message:viewAllMessages', 'Your message has been sent succesfully');
		}
	}

	/**
	 * 'Deletes' a message from a user's inbox. Doesn't actually delete the message, as messages
	 * are objects that are shared amongst many users, but instead marks it as 'deleted' and
	 * removes it from a user's received messages
	 * 
	 * @param Request $request
	 * @param int $messageid
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function deleteMessageAction(Request $request, $messageid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$messages = $user->getReceivedMessages();

		// make sure the message exists
		$em = $this->getDoctrine()->getManager();
		$message = $em->getRepository('IMDCTerpTubeBundle:Message')->findOneById($messageid);

		if (!$messages->contains($message)) 
		{
			throw $this->createNotFoundException('No message found for id: ' . $messageid);
		}
		// make sure the current user is a recipient on this message
		else if (!$message->getRecipients()->contains($user)) 
		{
			throw $this
					->createNotFoundException('User is not found in the recipients for this message id: ' . $messageid);
		}

		// remove message from user's received messages
		$user->removeReceivedMessage($message);
		
		// remove message from archived messages (if archived)
		$user->removeArchivedMessage($message);


		// add message to users deleted messages
		$user->addDeletedMessage($message);

		$em->persist($user);
		$em->flush();

		$this->get('session')->getFlashBag()->add('inbox', 'Message id: ' . $message->getId() . ' deleted');
		return $this->redirect($this->generateUrl('imdc_message_view_all'));

	}

	public function archiveMessageAction(Request $request, $messageid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$messages = $user->getReceivedMessages();

		$em = $this->getDoctrine()->getManager();
		$message = $em->getRepository('IMDCTerpTubeBundle:Message')->findOneById($messageid);

		if (!$messages->contains($message))
		{
			throw $this->createNotFoundException('No message found for id: ' . $messageid);
		}

		/*         $em->getRepository('IMDCTerpTubeBundle:Message')
		 ->deleteMessageFromInbox($messageid, $this->getUser()->getId()); */

		//$message->addUsersArchived($user);
		$user->addArchivedMessage($message);
		$user->removeReceivedMessage($message);

		//$em->persist($message);
		$em->persist($user);
		$em->flush();

		$this->get('session')->getFlashBag()
				->add('inbox', 'Message id:' . $message->getId() . ' successfully archived');
		return $this->redirect($this->generateUrl('imdc_message_view_all'));
	}

	public function viewSentMessagesAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$messages = $user->getSentMessages();

		//return $this->render('IMDCTerpTubeBundle:Message:sentmessages.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Message:index.html.twig', array(
            'messages' => $messages,
            'tab' => 'sent'
        ));
	}

	public function viewArchivedMessagesAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		// todo: implement message archive system
		$messages = $user->getArchivedMessages();

        //return $this->render('IMDCTerpTubeBundle:Message:archivedmessages.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Message:index.html.twig', array(
            'messages' => $messages,
            'tab' => 'archive'
        ));
	}

	public function viewMessageAction(Request $request, $messageid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$message = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message')
				->findOneBy(array('id' => $messageid));

		// does the user have access to read this message?
		if ($message->getRecipients()->contains($user) 
		        || $message->getOwner() == $user) {
		    
		    // is this message deleted already?
		    if ($user->getDeletedMessages()->contains($message)) {
		        $this->get('session')->getFlashBag()->add(
		            'error',
		            'This message has been deleted'
		        );
		        return $this->redirect($this->generateUrl('imdc_message_view_all'));
		    }
		            
		    $alreadyRead = $user->getReadMessages()->contains($message);
		    
		    // if the message is already read, don't try to insert it again as this causes SQL errors
		    if (!$alreadyRead)
		    {
		        $user->addReadMessage($message);
		        // flush object to database
		        $em = $this->getDoctrine()->getManager();
		        $em->persist($user);
		        $em->flush();
		    }

            //return $this->render('IMDCTerpTubeBundle:Message:viewprivatemessage.html.twig', array(
            return $this->render('IMDCTerpTubeBundle:_Message:view.html.twig', array(
                'message' => $message
            ));
		}
		else {
		    $this->get('session')->getFlashBag()->add(
		            'error',
		            'You do not have permission to view this message'
		    );
		    return $this->redirect($this->generateUrl('imdc_message_view_all'));
		}

	}

	public function isMessageReadAction($messageid)
	{
		$user = $this->getUser();
		$request = $this->get('request');
		$mid = $request->request->get('msgId');

		$repository = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message');
		$message = $repository->findOneById($mid);

		if ($message->isMessageRead($user))
		{
			$return = array('responseCode' => 200, 'feedback' => 'Message marked as read');
		}
		else
		{
			$return = array('responseCode' => 400, 'feedback' => 'Error marking message as read');
		}

		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json')); //make sure it has the correct content type
	}

	public function replyToMessageAction(Request $request, $messageid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$message = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message')
				->findOneBy(array('id' => $messageid));

		// todo: make sure the user is a recipient of the message?

		$form = $this->createForm(new PrivateMessageReplyType(), $message, array(
		    'em' => $this->getDoctrine()->getManager(),
		    'user' => $user,
		));
		
		$form->setData($message);

		$form->handleRequest($request);

		if ($form->isValid())
		{ // if the form is successfully submitted

			$messagereply = new Message();
			// set sentDate of message to be when form request received
			// this is automatically set again when the object is inserted into the database anyway
			$messagereply->setSentDate(new \DateTime('now'));

			$messagereply->setSubject($message->getSubject());
			$messagereply->setContent($message->getContent());
			$messagereply->setOwner($this->getUser());

			// set owner/author of the message to be the currently logged in user
			$messagereply->setOwner($this->getUser());

			$em = $this->getDoctrine()->getManager();

			$user = $this->getUser();
			$user->addSentMessage($messagereply);

			foreach ($message->getRecipients() as $recp) {
			    $recp->addReceivedMessage($messagereply);
			    // request persistence of user object to database
			    $em->persist($recp);
			}
			
			foreach ($message->getAttachedMedia() as $possMedia) {
			    if ($possMedia === null) {
			        $message->getAttachedMedia()->removeElement($possMedia);
			    }
			}

			// request to persist message object to database
			$em->persist($messagereply);

			// persist all objects to database
			$em->flush();

			$this->get('session')->getFlashBag()->add('inbox', 'Message sent successfully!');
			
			return $this->redirect($this->generateUrl('imdc_message_view_all'));
		}

		// form not valid, show the basic form
		//return $this->render('IMDCTerpTubeBundle:Message:replytoprivatemessage.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Message:new.html.twig', array(
            'form' => $form->createView(),
            'message' => $message,
            'isReply' => true
        ));
	}
}
