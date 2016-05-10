<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController
 * @package IMDC\TerpTubeBundle\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class HomeController extends Controller
{
    /**
     * @return RedirectResponse|Response
     */
    public function indexAction()
	{
		// check if the user is logged in
		if (!$this->container->get('imdc_terptube.authentication.manager')->isAuthenticated()) {
			return $this->redirect($this->generateUrl('imdc_default_index'));
		}

		$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $securityContext = $this->get('security.context');
        $limit = 4;

        $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')
            ->getViewableToUser($user, $securityContext, array(
                'sort' => 'f.lastActivity',
                'direction' => 'desc',
                'limit' => $limit
            ));

        $groups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')
            ->getViewableToUser($user, $securityContext, array(
                'sort' => 'g.dateCreated',
                'direction' => 'desc',
                'limit' => $limit
            ));

		return $this->render('IMDCTerpTubeBundle:Home:index.html.twig', array(
            'forums' => $forums,
            'forumThreadCount' => $em->getRepository('IMDCTerpTubeBundle:Thread')
                    ->getViewableCountForForums($forums, $securityContext),
            'groups' => $groups
        ));
	}
}
