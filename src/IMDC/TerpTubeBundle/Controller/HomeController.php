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
        $securityContext = $this->get('security.context');

		$myForums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getRecentlyCreatedForums(4);
        $myGroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getGroupsForUser($this->getUser(), 4);

        //FIXME this seems too costly just for the result of numeric convenience
        $forumThreadCount = array();
        $threadRepo = $em->getRepository('IMDCTerpTubeBundle:Thread');
        foreach ($myForums as $forum) {
            $forumThreadCount[] = count($threadRepo->getViewableToUser($securityContext, $forum->getId()));
        }

		return $this->render('IMDCTerpTubeBundle:Home:index.html.twig', array(
            'myForums' => $myForums,
            'myGroups' => $myGroups,
            'forumThreadCount' => $forumThreadCount
        ));
	}
}
