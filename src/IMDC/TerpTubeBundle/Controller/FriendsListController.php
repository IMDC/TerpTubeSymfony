<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Model\UserManager;
use IMDC\TerpTubeBundle\Entity;



class FriendsListController extends Controller
{
	public function addAction($userid) {
	    
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
	    $user = new \IMDC\TerpTubeBundle\Entity\User;
	    
	    $user = $this->getUser();
	    
	    $userManager = $this->container->get('fos_user.user_manager');
	    
	    $usertoadd = $userManager->findUserBy(array('id' => $userid));
	    
	    $user->addFriendsList($usertoadd);
	    
	    // flush object to database
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($user);
	    $em->flush();
	    
	    return $this->redirect($this->generateUrl('imdc_terp_tube_user_profile_specific', array('userName'=>$usertoadd->getUserName())));
	}
	
	public function removeAction($userid) 
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
	    $user = new \IMDC\TerpTubeBundle\Entity\User;
	     
	    $user = $this->getUser();
	     
	    $userManager = $this->container->get('fos_user.user_manager');
	     
	    $usertoremove = $userManager->findUserBy(array('id' => $userid));
	     
	    $user->removeFriendsList($usertoremove);
	     
	    // flush object to database
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($user);
	    $em->flush();
	     
	    return $this->redirect($this->generateUrl('imdc_terp_tube_user_profile_specific', array('userName'=>$usertoremove->getUserName())));
	}

}