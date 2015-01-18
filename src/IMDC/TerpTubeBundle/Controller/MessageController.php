<?php

namespace IMDC\TerpTubeBundle\Controller;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
	public function newAction(Request $request, $username = null, $recipients = null)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();

		$usersSelectForm = $this->createForm(new UsersSelectType(), null, array('em' => $em));
		$usersSelectForm->handleRequest($request);

		if ($usersSelectForm->isValid()) {
			$recipients = $usersSelectForm->get('users')->getData();
		}
		
		$message = new Message();
		$form = $this->createForm(new PrivateMessageType(), $message, array(
            'em' => $em
		));
		$form->handleRequest($request);

        if (!$form->isValid()) {
            if ($username) {
                $user = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('username' => $username));
                if ($user) {
                    $form->get('recipients')->setData(array($user));
                }
            }

            if ($recipients) {
                $form->get('recipients')->setData($recipients);
            }
        } else {
            $user = $this->getUser();

			$message->setSentDate(new \DateTime('now'));
			$message->setOwner($user);

			foreach ($message->getRecipients() as $recipient) {
			    $recipient->addReceivedMessage($message);
			    $em->persist($recipient);
			}

            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$message->getAttachedMedia()->contains($media))
                    $message->addAttachedMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('attachedMedia')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $user->addSentMessage($message);

			$em->persist($message);
            $em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
                'success', 'Message sent successfully!'
            );

			return $this->redirect($this->generateUrl('imdc_message_inbox'));
		}

		return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array(
            'form' => $form->createView()
        ));
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

        return $this->render('IMDCTerpTubeBundle:Message:index.html.twig', array(
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

        //FIXME ensure that the message is only removed from the users sent/received/archived list

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

		$this->get('session')->getFlashBag()->add('info', 'Message id: ' . $message->getId() . ' deleted');
		return $this->redirect($this->generateUrl('imdc_message_inbox'));

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
		return $this->redirect($this->generateUrl('imdc_message_inbox'));
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

		return $this->render('IMDCTerpTubeBundle:Message:index.html.twig', array(
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

        return $this->render('IMDCTerpTubeBundle:Message:index.html.twig', array(
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
		            'danger',
		            'This message has been deleted'
		        );
		        return $this->redirect($this->generateUrl('imdc_message_inbox'));
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

            return $this->render('IMDCTerpTubeBundle:Message:view.html.twig', array(
                'message' => $message
            ));
		}
		else {
		    $this->get('session')->getFlashBag()->add(
		            'danger',
		            'You do not have permission to view this message'
		    );
		    return $this->redirect($this->generateUrl('imdc_message_inbox'));
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
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

        $em = $this->getDoctrine()->getManager();
		$origMessage = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:Message')->find($messageid);
        if (!$origMessage) {
            throw new \Exception('message not found');
        }

        $user = $this->getUser();
        if (!$origMessage->getRecipients()->contains($user)) {
            throw new AccessDeniedException();
        }

        //TODO maybe create a message copy function?
        $message = new Message();
        $message->setSubject($origMessage->getSubject());
        $message->setContent($origMessage->getContent());
        foreach ($origMessage->getRecipients() as $recipient) {
            $message->addRecipient($recipient);
        }
        foreach ($origMessage->getAttachedMedia() as $media) {
            $message->addAttachedMedia($media);
        }

        $message->removeRecipient($user);
        if ($origMessage->getOwner()->getId() != $user->getId()) {
            $message->addRecipient($origMessage->getOwner());
        }

		$form = $this->createForm(new PrivateMessageType(), $message, array(
		    'em' => $em
		));
		$form->handleRequest($request);

		if ($form->isValid()) {
            $message->setSentDate(new \DateTime('now'));
            $message->setOwner($user);

            foreach ($message->getRecipients() as $recipient) {
                $recipient->addReceivedMessage($message);
			    $em->persist($recipient);
			}

			/*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$message->getAttachedMedia()->contains($media))
                    $message->addAttachedMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('attachedMedia')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $user->addSentMessage($message);

			$em->persist($message);
            $em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
                'info', 'Message sent successfully!'
            );
			
			return $this->redirect($this->generateUrl('imdc_message_inbox'));
		}

		return $this->render('IMDCTerpTubeBundle:Message:new.html.twig', array(
            'form' => $form->createView(),
            'message' => $message,
            'isReply' => true
        ));
	}
}
