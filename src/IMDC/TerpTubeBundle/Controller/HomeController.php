<?php

namespace IMDC\TerpTubeBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomeController extends Controller
{
	public function indexAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();

		$recentPosts = $em->getRepository('IMDCTerpTubeBundle:Post')->getRecentPosts(4);

		$recentThreads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getMostRecentThreads(4);

		$recentForums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getMostRecentForums(4);
		
		return $this
				->render('IMDCTerpTubeBundle:Default:recentactivity.html.twig',
						array('forums' => $recentForums, 'posts' => $recentPosts, 'threads' => $recentThreads));

		//return $this->render('IMDCTerpTubeBundle:Home:index.html.twig');

	}
}
