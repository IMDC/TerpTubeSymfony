<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class HelpController extends Controller
{
    /**
     * Matches the route for /
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function indexAction(Request $request)
	{
        return $this->render('IMDCTerpTubeBundle:_Help:index.html.twig');
	}

}
