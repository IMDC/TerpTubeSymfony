<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IMDC\TerpTubeBundle\Entity\UserGroupRepository;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * This basic controller shows the home splash page of TerpTube when a user is not
 * logged in
 * 
 * @author paul
 *
 */
class DefaultController extends Controller
{
    /**
     * Matches the route for /
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function indexAction(Request $request)
	{
        if ($this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('imdc_terp_tube_user_splash'));
        }

        $formFactory = $this->container->get('fos_user.registration.form.factory');
        $form = $formFactory->createForm();

		return $this->render('IMDCTerpTubeBundle:_Default:index.html.twig', array(
            'form' => $form->createView()
        ));
	}

}
