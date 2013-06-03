<?php

namespace IMDC\TerpTubeBundle\MartinBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('IMDCTerpTubeBundleMartinBundle:Default:index.html.twig');
    }
}
