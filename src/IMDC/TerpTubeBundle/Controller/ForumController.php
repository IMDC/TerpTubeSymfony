<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Form\Type\ForumFormType;
use IMDC\TerpTubeBundle\Form\Type\ForumFormDeleteType;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Permissions;
use IMDC\TerpTubeBundle\Entity\ForumRepository;
use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;

/**
 * Controller for all Forum object related actions such as new, edit, delete
 * 
 * @author paul
 *
 */
class ForumController extends Controller
{
	public function listAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IMDCTerpTubeBundle:Forum');

		$recentForums = $repo->getMostRecentForums(4); //FIXME
		$forums = $repo->getViewableToUser($this->getUser());
		
		$paginator = $this->get('knp_paginator');
		$forums = $paginator->paginate(
            $forums,
			$this->get('request')->query->get('page', 1), /*page number*/
			8 /*limit per page*/
		);

		return $this->render('IMDCTerpTubeBundle:Forum:index.html.twig', array(
            'recentForums' => $recentForums,
			'forums' => $forums
		));
	}
	
	public function newAction(Request $request, $groupId)
	{
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

        $em = $this->getDoctrine()->getManager();

        $forum = new Forum();
        $forumForm = $this->createForm(new ForumFormType(), $forum, array(
            'em' => $em,
            'groupId' => $groupId
        ));
        $forumForm->handleRequest($request);
        
        if ($forumForm->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $forum->setCreator($user);
            $forum->setLastActivity($currentDateTime);
            $forum->setCreationDate($currentDateTime);

            $media = $forumForm->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);
            }

            $user->addForum($forum);

            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($forum);
            $acl = $aclProvider->createAcl($objectIdentity);

            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
            
            $this->get('session')->getFlashBag()->add(
                'info', 'Forum created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }
        
        return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig', array(
            'form' => $forumForm->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
	}
	
	public function viewAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    // setup necessary vars
	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	    $forumRepo = $em->getRepository('IMDCTerpTubeBundle:Forum');
	    
	    // retrieve forum from storage layer
	    $forum = $forumRepo->findOneBy(array('id' => $forumid));
	    
	    if (!$forum) {
	        $this->get('session')->getFlashBag()->add(
	            'info',
	            'A forum with this identification does not exist!'
	        );

	        return $this->redirect($this->generateUrl('imdc_forum_list'));
	    }
	    
	    //FIXME: implement forum permissions check here when permissions enabled for forums
	    
	    $recentThreads = $em->getRepository('IMDCTerpTubeBundle:Thread')->findRecentThreadsForForum($forum->getId(), 4);
	    
	    $threads = $forumRepo->getTopLevelThreadsForForum($forum->getId());
	    $filteredThreads = array();
	    
	    foreach ($threads as $thread) {
            // check for the correct permissions access
            if ($thread->visibleToUser($user)) {
                $filteredThreads[] = $thread; // push onto end of array
            }
	    }
	    
	    $paginator = $this->get('knp_paginator');
	    $threads = $paginator->paginate(
	    	$filteredThreads,
	    	$this->get('request')->query->get('page', 1) /* page number */,
	    	8, /* limit per page */
	    	array('distinct' => false)
	    );
	        
	    return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
	    	'forum' => $forum,
	    	'recentThreads' => $recentThreads,
	    	'threads' => $threads
	    ));
	}
	
	public function editAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    $securityContext = $this->get('security.context');
	    if (false === $securityContext->isGranted('EDIT', $forumid)) {
	        throw new AccessDeniedException();
	    }

        $em = $this->getDoctrine()->getManager();

	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
	    $forumForm = $this->createForm(new ForumFormType(), $forum, array(
            'em' => $em
        ));
        $forumForm->handleRequest($request);
	    
	    if ($forumForm->isValid()) {
            $user = $this->getUser();
            $forum->setLastActivity(new \DateTime('now'));

            $media = $forumForm->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);
            }

	        $em->persist($forum);
	        $em->persist($user);
	        $em->flush();
	        
	        $this->get('session')->getFlashBag()->add(
                'info', 'Forum edited successfully!'
            );

	        return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }
	        
        return $this->render('IMDCTerpTubeBundle:Forum:edit.html.twig', array(
            'form' => $forumForm->createView(),
            'forum' => $forum,
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
	}
	
	public function deleteAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }

	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	    
	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->findOneBy(array('id' => $forumid));

	    // check if user is the creator of the forum
	    if ($user !== $forum->getCreator()) {
	        $this->get('session')->getFlashBag()->add(
	            'danger',
	            'You are not authorized to delete this forum'
	        );
	        
	        return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
                'forum' => $forum
            ));
	    }
	    
	    $form = $this->createForm(new ForumFormDeleteType(), $forum);
	    $form->handleRequest($request);

	    if ($form->isValid()) {
	        $threads = $forum->getThreads();
	        foreach ($threads as $thread) {
	            $threadposts = $thread->getPosts();
	            foreach ($threadposts as $threadpost) {
	                $threadpost->getAuthor()->removePost($threadpost);
	                $em->remove($threadpost);
	            }
	            $thread->getCreator()->removeThread($thread);
	            $em->remove($thread);
	        }
	        $user->removeForum($forum);
	        $em->persist($user);
	        $em->remove($forum);

	        // complete all deletions and persist user object to database
	        $em->flush();

	        $this->get('session')->getFlashBag()->add(
	            'info',
	            'Forum deleted successfully!'
	        );

	        return $this->redirect($this->generateUrl('imdc_forum_list'));
	    }

	    return $this->render('IMDCTerpTubeBundle:Forum:delete.html.twig', array(
            'form' => $form->createView(),
            'forum' => $forum
        ));
	}
}
