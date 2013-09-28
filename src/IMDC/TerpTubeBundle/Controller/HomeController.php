<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class HomeController extends Controller
{
    public function indexAction()
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
        
		$em = $this->getDoctrine()->getManager();
		 
		$recentPosts = $em->getRepository('IMDCTerpTubeBundle:Post')
		->getRecentPosts(3);
		 
		$recentThreads = $em->getRepository('IMDCTerpTubeBundle:Thread')
		->getMostRecentThreads(3);
		 
		return $this->render('IMDCTerpTubeBundle:Default:recentactivity.html.twig',
				array('posts' => $recentPosts,
						'threads' => $recentThreads)
		);
		
		//return $this->render('IMDCTerpTubeBundle:Home:index.html.twig');
		
    }
}
