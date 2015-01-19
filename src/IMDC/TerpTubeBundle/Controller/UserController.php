<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Model\UserManager;
use IMDC\TerpTubeBundle\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for User object actions
 * 
 * @author paul
 *
 */
class UserController extends Controller
{
    /**
     * The index action lists profiles of members who have chosen to have their
     * profile publicly displayed
     * 
     * @param Request $request
     * @param string $page
     */
    public function indexAction(Request $request, $page=null)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        /**
         Need the count query to provide a manual count of items 
         to the paginator
         */
        $count = $em->createQuery('
                            SELECT count(u)
                            FROM IMDCTerpTubeBundle:User u
                            JOIN IMDCTerpTubeBundle:UserProfile p
                            WHERE u.profile IS NOT NULL 
                            AND u.profile = p
                            AND p.profileVisibleToPublic = 1
                         ')
                    ->getSingleScalarResult();

        $dql = "SELECT u
                FROM IMDCTerpTubeBundle:User u
                JOIN IMDCTerpTubeBundle:UserProfile p
                WHERE u.profile IS NOT NULL 
                AND u.profile = p
                AND p.profileVisibleToPublic = 1";
        
        //Count Hint is ignored in newer version of paginator.
        $query = $em->createQuery($dql)->setHint('knp_paginator.count', $count);
        
        $paginator = $this->get('knp_paginator');
        $members = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /* page number */,
            12, /* limit per page */
            array('distinct' => false)
        );
        
        return $this->render('IMDCTerpTubeBundle:Member:index.html.twig', array(
            'members' => $members
        ));
    }
    
    
    public function mentorRemoveAction(Request $request, $id)
    {        
		$userManager = $this->container->get('fos_user.user_manager');

		$userToRemove = $userManager->findUserBy(array('id' => $id));
		
		if (!$userToRemove) {
            throw $this->createNotFoundException('Unable to find user.');
		}
        
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        if ($user->isUserOnMentorList($userToRemove)) {
            $user->removeMentorList($userToRemove);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Mentor removed');
        }
        else {
            $this->get('session')->getFlashBag()->add('danger', 'User is not your mentor, nothing to remove');
        }

        //return $this->redirect($this->generateUrl('imdc_invitation_list'));
        $url = $this->getRequest()->headers->get("referer");
        return new RedirectResponse($url);
    }

    
    public function menteeRemoveAction(Request $request, $id)
    {
        $userManager = $this->container->get('fos_user.user_manager');

		$userToRemove = $userManager->findUserBy(array('id' => $id));
		
		if (!$userToRemove) {
            throw $this->createNotFoundException('Unable to find user.');
		}
        
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        if ($user->isUserOnMenteeList($userToRemove)) {
            $user->removeMenteeList($userToRemove);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Mentee removed');
            
        }
        else {
            $this->get('session')->getFlashBag()->add('danger', 'User is not your mentee, nothing to remove');
        }

        //return $this->redirect($this->generateUrl('imdc_invitation_list'));
        $url = $this->getRequest()->headers->get("referer");
        return new RedirectResponse($url);
    }
}