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
use IMDC\TerpTubeBundle\Controller\MediaChooserGatewayController;

/**
 * Controller for all Forum object related actions such as new, edit, delete
 * 
 * @author paul
 *
 */
class ForumController extends Controller
{
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
		$recentForums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getMostRecentForums(4);
		
		$dql = "SELECT f FROM IMDCTerpTubeBundle:Forum f ORDER BY f.lastActivity DESC";
		$query = $em->createQuery($dql);
		
		$paginator = $this->get('knp_paginator');
		$forums = $paginator->paginate(
			$query,
			$this->get('request')->query->get('page', 1), /*page number*/
			8 /*limit per page*/
		);

		//return $this->render('IMDCTerpTubeBundle:Forum:index.html.twig', array(
		return $this->render('IMDCTerpTubeBundle:_Forum:index.html.twig', array(
            'recentForums' => $recentForums,
			'forums' => $forums
		));
	}
	
	public function newAction(Request $request) 
	{
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

        $newforum = new Forum();
        $form = $this->createForm(new ForumFormType(), $newforum);

        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
        	
            $mediarepo = $em->getRepository('IMDCTerpTubeBundle:Media');
            // if the media text area isn't empty, the user has selected a media
            // file to create a new thread with
            if (!$form->get('mediatextarea')->isEmpty()) {
                $rawmediaID = $form->get('mediatextarea')->getData();
                $logger = $this->container->get('logger');
                $logger->info('*************media id is ' . $rawmediaID);
                /** @var $mediaFile IMDC\TerpTubeBundle\Entity\Media */
                $mediaFile = new Media();
                $mediaFile = $mediarepo->findOneBy(array('id' => $rawmediaID));
                
                // check to make sure the user owns this media file
                if ($user->getResourceFiles()->contains($mediaFile)) {
                    $logger = $this->get('logger');
                    $logger->info('User owns this media file');
                    $newforum->addTitleMedia($mediaFile);
                }
            }

            $newforum->setCreator($user);
            $newforum->setCreationDate(new \DateTime('now'));
            //$newforum->setLocked(FALSE); // not currently in model
            //$newforum->setSticky(FALSE); // not currently in model
            $newforum->setLastActivity(new \DateTime('now'));

            $user->addForum($newforum);
            //$user->increasePostCount(1);

            // request to persist message object to database
            $em->persist($newforum);
            $em->persist($user);

            // persist all objects to database
            $em->flush();

            // creating the ACL which is not currently used for access restrictions
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($newforum);
            $acl = $aclProvider->createAcl($objectIdentity);
            
            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($user);
            
            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
            
            $this->get('session')->getFlashBag()->add(
                'info',
                'Forum created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_forum_view_specific', array(
                'forumid' => $newforum->getId()
            )));
        }
        
        // form not valid, show the basic form
        //return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Forum:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MediaChooserGatewayController::getUploadForms($this)
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
	        
	    //return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
	    return $this->render('IMDCTerpTubeBundle:_Forum:view.html.twig', array(
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
	    
	    // check for edit access using ACL
	    if (false === $securityContext->isGranted('EDIT', $forumid)) {
	        throw new AccessDeniedException();
	    }

        $em = $this->getDoctrine()->getManager();

	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->findOneBy(array('id' => $forumid));
	    
	    $form = $this->createForm(new ForumFormType(), $forum);
	    $form->handleRequest($request);
	    
	    if ($form->isValid()) {
            $user = $this->getUser();

	        $mediarepo = $em->getRepository('IMDCTerpTubeBundle:Media');
	        // if the media text area isn't empty, the user has selected a new media
	        // file to use for the forum title
	        if (!$form->get('mediatextarea')->isEmpty()) {
	            $rawmediaID = $form->get('mediatextarea')->getData();
	            $logger = $this->container->get('logger');
	            $logger->info('*************media id is ' . $rawmediaID);
	            /** @var $mediaFile IMDC\TerpTubeBundle\Entity\Media */
	            $mediaFile = new Media();
	            $mediaFile = $mediarepo->findOneBy(array('id' => $rawmediaID));
	        
	            // check to make sure the user owns this media file
	            if ($user->getResourceFiles()->contains($mediaFile)) {
	                $logger = $this->get('logger');
	                $logger->info('User owns this media file');
	                $forum->addTitleMedia($mediaFile);
	            }
	        }

	        //$newforum->setCreator($user);
	        //$newforum->setCreationDate(new \DateTime('now'));
	        //             $newforum->setLocked(FALSE);
	        //             $newforum->setSticky(FALSE);
	        $forum->setLastActivity(new \DateTime('now'));

	        //$user->addForum($newforum);
	        //             $user->increasePostCount(1);

	        // request to persist message object to database
	        $em->persist($forum);
	        $em->persist($user);

	        // persist all objects to database
	        $em->flush();
	        
	        $this->get('session')->getFlashBag()->add(
	            'info',
	            'Forum edited successfully!'
	        );

	        return $this->redirect($this->generateUrl('imdc_forum_view_specific', array(
                'forumid' => $forum->getId()
            )));
        }
	        
        // form not valid, show the basic form
        //return $this->render('IMDCTerpTubeBundle:Forum:edit.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Forum:edit.html.twig', array(
            'form' => $form->createView(),
            'forum' => $forum,
            'uploadForms' => MediaChooserGatewayController::getUploadForms($this)
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
	        
	        //return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
            return $this->render('IMDCTerpTubeBundle:_Forum:view.html.twig', array(
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

	    // form not valid, show the basic form
	    //return $this->render('IMDCTerpTubeBundle:Forum:delete.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Forum:delete.html.twig', array(
            'form' => $form->createView(),
            'forum' => $forum
        ));
	}
}
