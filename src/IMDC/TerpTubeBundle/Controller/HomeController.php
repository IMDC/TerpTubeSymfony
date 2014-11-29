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
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('imdc_index'));
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
