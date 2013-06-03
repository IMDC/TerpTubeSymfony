<?php

namespace IMDC\TerpTubeBundle\MartinBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('IMDCTerpTubeBundleMartinBundle:Test:index.html.twig', array('name' => $name));
    }
}
