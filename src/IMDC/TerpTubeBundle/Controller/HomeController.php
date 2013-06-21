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
    	$securityContext = $this->container->get('security.context');
		if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			return $this->render('IMDCTerpTubeBundle:Home:index.html.twig');
//			return new Response('<html><body>Hello!</body></html>');
		}
		else 
		{
			$response = new RedirectResponse($this->generateUrl('imdc_terp_tube_homepage')); 
			return $response;
			//return new Response('<html><body>Bye!</body></html>');
		}

        // return $this->render('<html><body>Hello world</body></html>');
        //return new Response('<html><body>Hello!</body></html>');
        // return array();
    }
}
