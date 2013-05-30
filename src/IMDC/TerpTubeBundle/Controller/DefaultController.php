<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DefaultController extends Controller
{
     /**
     * @Route("/{name}", name="_paultest")
     */
    public function indexAction($name)
    {
        //return $this->render('IMDCTerpTubeBundle:Default:index.html.twig', array('name' => $name));
        // return $this->render('<html><body>Hello world</body></html>');
        return new Response('<html><body>Hello ' . $name . '!</body></html>');
        // return array();
    }
}
