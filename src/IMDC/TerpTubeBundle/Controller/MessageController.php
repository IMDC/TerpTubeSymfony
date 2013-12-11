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

class MessageController extends Controller
{
	public function createMessageAction(Request $request, $userid = null)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$message = new Message();
		//$form = $this->createForm(new PrivateMessageType(), $message);

		
		$form = $this->createForm(new PrivateMessageType(), $message, array(
		        'em' => $this->getDoctrine()->getManager(),
		));
		

		$form->handleRequest($request);

		if ($form->isValid())
		{

			// set sentDate of message to be when form request received
			// this is automatically set again when the object is inserted into the database anyway
			$message->setSentDate(new \DateTime('now'));

			// set owner/author of the message to be the currently logged in user
			$message->setOwner($this->getUser());

			$em = $this->getDoctrine()->getManager();

			$user = $this->getUser();
			$user->addSentMessage($message);

			/*
			$existingUsers = new \Doctrine\Common\Collections\ArrayCollection();
			$userProvider = $this->container->get('fos_user.user_provider.username');

			// split up the recipients by whitespace
			$rawrecips = explode(' ', $form->get('to')->getData());
			foreach ($rawrecips as $possuser)
			{
				try
				{
					$theuser = $userProvider->loadUserByUsername($possuser);
					$existingUsers[] = $theuser;
					$message->addRecipient($theuser);
				}
				catch (UsernameNotFoundException $e)
				{
					// todo: create message to user about recip not found
				}
			}

		    
			foreach ($existingUsers as $euser)
			{
				$euser->addReceivedMessage($message);
				$em->persist($euser);
			}
			*/
			
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
		return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array('form' => $form->createView(),));

	}

	public function createMessageToAction(Request $request, $userid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$message = new Message();

		$form = $this->createForm(new PrivateMessageType(), $message);

		$form->handleRequest($request);

		if ($form->isValid())
		{

			// set sentDate of message to be when form request received
			// this is automatically set again when the object is inserted into the database anyway
			$message->setSentDate(new \DateTime('now'));

			// set owner/author of the message to be the currently logged in user
			$message->setOwner($this->getUser());

			$em = $this->getDoctrine()->getManager();

			$user = $this->getUser();
			$user->addSentMessage($message);

			$existingUsers = new \Doctrine\Common\Collections\ArrayCollection();
			$userManager = $this->container->get('fos_user.user_manager');

			// split up the recipients by whitespace
			$rawrecips = explode(' ', $form->get('to')->getData());
			foreach ($rawrecips as $possuser)
			{
				try
				{
					$theuser = $userManager->loadUserByUsername($possuser);
					$existingUsers[] = $theuser;
					$message->addRecipient($theuser);
				}
				catch (UsernameNotFoundException $e)
				{
					// todo: create message to user about recip not found
				}
			}

			foreach ($existingUsers as $euser)
			{
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

		$messages = $em->getRepository('IMDCTerpTubeBundle:User')->getMostRecentInboxMessages($user, 30);

		// if no feedback message, just show all messages
		if (is_null($feedbackmsg))
		{
			$response = $this->render('IMDCTerpTubeBundle:Message:inbox.html.twig', array('messages' => $messages));
			return $response;
		}
		// show all messages and the feedback message
		else
		{
			$response = $this
					->render('IMDCTerpTubeBundle:Message:inbox.html.twig',
							array('messages' => $messages, 'feedback' => $feedbackmsg));
			return $response;
		}
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

		// remove message from users received messages
		$user->removeReceivedMessage($message);

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

		// if no feedback message, just show all messages
		if (@is_null($feedbackmsg))
		{
			$response = $this
					->render('IMDCTerpTubeBundle:Message:sentmessages.html.twig', array('messages' => $messages));
			return $response;
		}
		// show all messages and the feedback message
		else
		{
			$response = $this
					->render('IMDCTerpTubeBundle:Message:sentmessages.html.twig',
							array('messages' => $messages, 'feedback' => $feedbackmsg));
			return $response;
		}
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

		$response = $this
				->render('IMDCTerpTubeBundle:Message:archivedmessages.html.twig', array('messages' => $messages));
		return $response;
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
		    
		    $alreadyRead = $user->getReadMessages()->contains($message);
		    
		    // if the message is already read, don't try to insert it again as this causes SQL errors
		    if ($alreadyRead)
		    {
		        // skip
		    }
		    else
		    {
		        $user->addReadMessage($message);
		        // flush object to database
		        $em = $this->getDoctrine()->getManager();
		        $em->persist($user);
		        $em->flush();
		    }
		    
		    $response = $this
		    ->render('IMDCTerpTubeBundle:Message:viewprivatemessage.html.twig', array('message' => $message));
		    return $response;
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
			
		    /*
			$existingUsers = new \Doctrine\Common\Collections\ArrayCollection();
			$userManager = $this->container->get('fos_user.user_manager');

			// split up the recipients by whitespace
			$rawrecips = split(' ', $form->get('to')->getData());
			foreach ($rawrecips as $possuser)
			{
				try
				{
					$theuser = $userManager->loadUserByUsername($possuser);
					$existingUsers[] = $theuser;
					$messagereply->addRecipient($theuser);
				}
				catch (UsernameNotFoundException $e)
				{
					// todo: create message to user about recip not found
				}
			}

			foreach ($existingUsers as $euser)
			{
				$euser->addReceivedMessage($messagereply);
				$em->persist($euser);
			}
			*/

			foreach ($message->getRecipients() as $recp) {
			    $recp->addReceivedMessage($message);
			    // request persistence of user object to database
			    $em->persist($recp);
			}

			// request to persist message object to database
			$em->persist($messagereply);

			// persist all objects to database
			$em->flush();

			$this->get('session')->getFlashBag()->add('inbox', 'Message sent successfully!');
			return $this->redirect($this->generateUrl('imdc_message_view_all'));

			//return new Response('Created message id '.$message->getId());
		}

		// form not valid, show the basic form
		return $this
				->render('IMDCTerpTubeBundle:Message:replytoprivatemessage.html.twig',
						array('form' => $form->createView(), 'message' => $message,));

		/*
		$response = $this->render('IMDCTerpTubeBundle:Message:replytoprivatemessage.html.twig',
		        array('message' => $message)
		);
		return $response;
		 */
	}

	public function recentMessagesAction($max = 30)
	{

	}
}
