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
    /**
     * The 'homepage' controller, shows recent activity in 3 areas: Forums, Threads, and Posts
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
	public function indexAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('imdc_index'));
		}

		$em = $this->getDoctrine()->getManager();

		$myForums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getRecentlyCreatedForums(4);
        $myGroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getGroupsForUser($this->getUser(), 4);

		return $this->render('IMDCTerpTubeBundle:_Home:index.html.twig', array(
            'myForums' => $myForums,
            'myGroups' => $myGroups
        ));
	}
}
