<?php

namespace IMDC\TerpTubeBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Model\UserManager;
use IMDC\TerpTubeBundle\Entity;

/**
 * Controller for all FriendsList actions
 * 
 * @author paul
 *
 */
class FriendsListController extends Controller
{
    /**
     * Add a user to the currently logged in user's friendlist
     * 
     * @param Request $request
     * @param unknown $userid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
	public function addAction(Request $request, $userid)    
	{

		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$user = new \IMDC\TerpTubeBundle\Entity\User;

		$user = $this->getUser();

		$userManager = $this->container->get('fos_user.user_manager');

		$usertoadd = $userManager->findUserBy(array('id' => $userid));
		
		$user->addFriendsList($usertoadd);

		// flush object to database
		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

	    return $this->redirect($this->generateUrl('imdc_terp_tube_user_profile_specific',
								        array('userName' => $usertoadd->getUserName())));
	}

	/**
	 * Remove a user from a friendslist
	 * 
	 * @param Request $request
	 * @param integer $userid
	 * @param string $redirect where to send the user after removed
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function removeAction(Request $request, $userid, $redirect)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$user = new \IMDC\TerpTubeBundle\Entity\User;

		$user = $this->getUser();

		$userManager = $this->container->get('fos_user.user_manager');

		$usertoremove = $userManager->findUserBy(array('id' => $userid));

		$user->removeFriendsList($usertoremove);

		// flush object to database
		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		if ($redirect == NULL)
		{
			return $this->redirect($this->generateUrl('imdc_terp_tube_user_profile_specific',
			    array('userName' => $usertoremove->getUserName()))
			);
		}

		return $this->redirect($this->generateUrl($redirect));
	}

	/**
	 * Show all people on friends list
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function showAllAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$user = new \IMDC\TerpTubeBundle\Entity\User;

		$user = $this->getUser();

		$usersFriends = $user->getFriendsList();

		$response = $this
				->render('IMDCTerpTubeBundle:FriendsList:showAll.html.twig', array('friends' => $usersFriends));
		return $response;

	}

}
