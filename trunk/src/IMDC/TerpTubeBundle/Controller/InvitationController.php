<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Entity\InvitationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvitationController
 *
 * @package IMDC\TerpTubeBundle\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class InvitationController extends Controller
{
	/**
	 *
	 * @param Request $request        	
	 * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function listAction(Request $request)
	{
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$invitations = $this->getUser ()->getReceivedInvitations ();
		$groups = array ();
		
		foreach ( $invitations as $invitation )
		{
			if ($invitation->getType ()->isGroup ())
			{
				$groups [$invitation->getId ()] = InvitationController::getGroupFromInviteData ( $this, $invitation );
			}
		}
		
		return $this->render ( 'IMDCTerpTubeBundle:Invitation:index.html.twig', array (
				'invitations' => $invitations,
				'groups' => $groups 
		) );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function createMentorInvitationAction(Request $request, $id)
	{
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userRecipient = $userManager->findUserBy ( array (
				'id' => $id 
		) );
		
		if (! $userRecipient)
		{
			throw $this->createNotFoundException ( 'Unable to find user.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = new Invitation ();
		$entity->setCreator ( $user );
		$entity->setRecipient ( $userRecipient );
		// $entity->setBecomeMentor(true);
		$entity->setType ( $em->getRepository ( 'IMDCTerpTubeBundle:InvitationType' )->find ( InvitationType::TYPE_MENTOR ) );
		
		$user->addCreatedInvitation ( $entity );
		$userRecipient->addReceivedInvitation ( $entity );
		
		$em->persist ( $user );
		$em->persist ( $userRecipient );
		$em->persist ( $entity );
		
		$em->flush ();
		
		$this->get ( 'session' )->getFlashBag ()->add ( 'success', 'Invitation sent' );
		
		$url = $this->getRequest ()->headers->get ( "referer" );
		return new RedirectResponse ( $url );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function createMenteeInvitationAction(Request $request, $id)
	{
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userRecipient = $userManager->findUserBy ( array (
				'id' => $id 
		) );
		
		if (! $userRecipient)
		{
			throw $this->createNotFoundException ( 'Unable to find user.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = new Invitation ();
		$entity->setCreator ( $user );
		$entity->setRecipient ( $userRecipient );
		// $entity->setBecomeMentee(true);
		$entity->setType ( $em->getRepository ( 'IMDCTerpTubeBundle:InvitationType' )->find ( InvitationType::TYPE_MENTEE ) );
		
		$user->addCreatedInvitation ( $entity );
		$userRecipient->addReceivedInvitation ( $entity );
		
		$em->persist ( $user );
		$em->persist ( $userRecipient );
		$em->persist ( $entity );
		
		$em->flush ();
		
		$this->get ( 'session' )->getFlashBag ()->add ( 'success', 'Invitation sent' );
		
		$url = $this->getRequest ()->headers->get ( "referer" );
		return new RedirectResponse ( $url );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse
	 * @throws \LogicException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
	 */
	public function acceptAction(Request $request, $id)
	{
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = $em->getRepository ( 'IMDCTerpTubeBundle:Invitation' )->find ( $id );
		
		if (! $entity)
		{
			throw $this->createNotFoundException ( 'Unable to find Invitation entity.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		
		// check to make sure this user is the target of the invitation
		if (! $user === $entity->getRecipient ())
		{
			throw new AccessDeniedException ( 'You do not have permission to accept this invitation' );
		}
		
		// check to make sure the invitation is not cancelled or declined
		if ($entity->getIsCancelled () || $entity->getIsDeclined ())
		{
			throw new \LogicException ( 'Invitation cannot be accepted' );
		}
		
		// deal with the actions of the invitation
		// if ($entity->getBecomeMentee()) {
		if ($entity->getType ()->isMentee ())
		{
			if (! $entity->getCreator ()->isUserOnMenteeList ( $entity->getRecipient () ))
			{ // recipient becomes mentee of the invitation creator
				$entity->getCreator ()->addMenteeList ( $entity->getRecipient () );
			}
			if (! $entity->getRecipient ()->isUserOnMentorList ( $entity->getCreator () ))
			{ // recipient becomes mentee of the invitation creator
			  // sender/creator becomes mentor to the recipient
				$entity->getRecipient ()->addMentorList ( $entity->getCreator () );
			}
			$em->persist ( $entity->getCreator () );
		}
		// elseif ($entity->getBecomeMentor()) {
		elseif ($entity->getType ()->isMentor ())
		{
			if (! $entity->getCreator ()->isUserOnMentorList ( $entity->getRecipient () ))
			{
				// recipient becomes mentor of the invitation creator
				$entity->getCreator ()->addMentorList ( $entity->getRecipient () );
			}
			if (! $entity->getRecipient ()->isUserOnMenteeList ( $entity->getCreator () ))
			{
				// sendor/creates becomes mentee to the recipient
				$entity->getRecipient ()->addMenteeList ( $entity->getCreator () );
			}
			
			$em->persist ( $entity->getCreator () );
		}
		elseif ($entity->getType ()->isGroup ())
		{
			$group = InvitationController::getGroupFromInviteData ( $this, $entity );
			
			$user->addUserGroup ( $group );
			// $group->addMember($user);
			
			// $em->persist($group);
		}
		
		// send message to invitation creator?
		$noReplyUser = $em->getRepository ( 'IMDCTerpTubeBundle:User' )->findOneBy ( array (
				'id' => 0 
		) );
		
		$messageSubject = $entity->getRecipient ()->getUsername () . ' has accepted your invitation';
		$messageContent = 'Congratulations, ' . $entity->getRecipient ()->getUsername () . ' has accepted your invitation!'; // TODO custom message based on invitation type
		
		$message = $entity->getCreator ()->createMessageToUser ( $noReplyUser, $messageSubject, $messageContent );
		$em->persist ( $message );
		
		$entity->setIsAccepted ( true );
		$entity->setIsDeclined ( false );
		$entity->setIsCancelled ( false );
		
		$em->persist ( $entity );
		$em->persist ( $user );
		
		$em->flush ();
		
		return $this->redirect ( $this->generateUrl ( 'imdc_invitation_list' ) );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse
	 * @throws \LogicException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
	 */
	public function declineAction(Request $request, $id)
	{
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = $em->getRepository ( 'IMDCTerpTubeBundle:Invitation' )->find ( $id );
		
		if (! $entity)
		{
			throw $this->createNotFoundException ( 'Unable to find Invitation entity.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		
		// check to make sure this user is the target of the invitation
		if (! $user === $entity->getRecipient ())
		{
			throw new AccessDeniedException ( 'You do not have permission to decline this invitation' );
		}
		
		// check to make sure invitation is not cancelled first
		if ($entity->getIsCancelled ())
		{
			throw new \LogicException ( 'Invitation was cancelled' );
		}
		
		$entity->setIsAccepted ( false );
		$entity->setIsDeclined ( true );
		$entity->setDateDeclined ( new \DateTime ( 'now' ) );
		$em->persist ( $entity );
		$em->flush ();
		
		return $this->redirect ( $this->generateUrl ( 'imdc_invitation_list' ) );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
	 */
	public function cancelAction(Request $request, $id)
	{
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = $em->getRepository ( 'IMDCTerpTubeBundle:Invitation' )->find ( $id );
		
		if (! $entity)
		{
			throw $this->createNotFoundException ( 'Unable to find Invitation entity.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		// check to make sure the invitation is not yet accepted
		if ($entity->getIsAccepted ())
		{
			$this->get ( 'session' )->getFlashBag ()->add ( 'danger', 'Invitation has already been accepted and cannot be cancelled.' );
			
			return $this->render ( 'IMDCTerpTubeBundle:Invitation:index.html.twig' );
		}
		
		$user = $this->getUser ();
		
		// make sure user is the creator of this invitation
		if (! $user === $entity->getCreator ())
		{
			throw new AccessDeniedException ( 'You do not have permission to cancel this invitation' );
		}
		
		$entity->setIsCancelled ( true );
		$em->persist ( $entity );
		$em->flush ();
		
		return $this->render ( 'IMDCTerpTubeBundle:Invitation:index.html.twig' );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param
	 *        	$id
	 * @return RedirectResponse
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
	 */
	public function reactivateAction(Request $request, $id)
	{
		$em = $this->getDoctrine ()->getManager ();
		
		$entity = $em->getRepository ( 'IMDCTerpTubeBundle:Invitation' )->find ( $id );
		
		if (! $entity)
		{
			throw $this->createNotFoundException ( 'Unable to find Invitation entity.' );
		}
		
		// check if the user is logged in
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request ))
		{
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		
		// make sure user is the creator of this invitation
		if (! $user === $entity->getCreator ())
		{
			throw new AccessDeniedException ( 'You do not have permission to reactivate this invitation' );
		}
		
		$entity->setIsCancelled ( false );
		$em->persist ( $entity );
		$em->flush ();
		
		return $this->redirect ( $this->generateUrl ( 'imdc_invitation_list' ) );
	}
	
	/**
	 *
	 * @param Controller $controller        	
	 * @param Invitation $invitation        	
	 * @return null
	 * @throws \Exception
	 */
	public static function getGroupFromInviteData(Controller $controller, Invitation $invitation)
	{
		$data = $invitation->getData ();
		if (! $data || ! isset ( $data ['groupId'] ))
		{
			throw new \Exception ( 'invalid invitation data' );
		}
		
		$em = $controller->getDoctrine ()->getManager ();
		$group = $em->getRepository ( 'IMDCTerpTubeBundle:UserGroup' )->find ( intval ( $data ['groupId'] ) );
		if (! $group)
		{
			// throw new \Exception('group not found');
			return null;
		}
		
		return $group;
	}
}
