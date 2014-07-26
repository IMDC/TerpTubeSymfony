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
use IMDC\TerpTubeBundle\Form\Type\AudioMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\VideoMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\OtherMediaFormType;

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
		
		$dql = "SELECT f FROM IMDCTerpTubeBundle:Forum f ORDER BY f.lastActivity DESC";
		$query = $em->createQuery($dql);
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
		    $query,
		    $this->get('request')->query->get('page', 1), /*page number*/
		    8 /*limit per page*/
		);
		
		$response = $this->render('IMDCTerpTubeBundle:Forum:index.html.twig',
		    array('pagination' => $pagination));
		
		return $response;
		
	}
	
	
	public function newAction(Request $request) 
	{
	    
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
        
        $user = $this->getUser();
        
        $newforum = new Forum();
        $form = $this->createForm(new ForumFormType(), $newforum, array(
//                 'user' => $this->getUser(),
        ));
        
        $formAudio = $this->createForm ( new AudioMediaFormType (), new Media (), array () );
        $formVideo = $this->createForm ( new VideoMediaFormType (), new Media (), array () );
        $formImage = $this->createForm ( new ImageMediaFormType (), new Media (), array () );
        $formOther = $this->createForm ( new OtherMediaFormType (), new Media (), array () );
        $uploadForms = array ( $formAudio->createView (), $formVideo->createView (), $formImage->createView (), $formOther->createView () );
        
        $em = $this->getDoctrine()->getManager();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            	
            //$em = $this->getDoctrine()->getManager();
        	
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
//             $newforum->setLocked(FALSE); // not currently in model
//             $newforum->setSticky(FALSE); // not currently in model
            $newforum->setLastActivity(new \DateTime('now'));
            	
            $user->addForum($newforum);
//             $user->increasePostCount(1);
            	
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
                    'notice',
                    'Forum created successfully!'
            );
            
            $newforumid = $newforum->getId();
//             return $this->redirect($this->generateUrl('imdc_forum_list'));
            return $this->redirect($this->generateUrl('imdc_forum_view_specific', array('forumid' => $newforumid)));
        }
        
        // form not valid, show the basic form
        return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig',
                array('form' => $form->createView(),
                		'uploadForms' => $uploadForms
        ));
	    
	}
	
	public function viewAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
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
	            'notice',
	            'A forum with this identification does not exist!'
	        );
	         
	        return $this->redirect($this->generateUrl('imdc_forum_list'));
	    }
	    
	    //FIXME: implement forum permissions check here when permissions enabled for forums
	    
	    $threads = $forumRepo->getTopLevelThreadsForForum($forum->getId());
	    $filteredThreads = array();
	    
	    foreach ($threads as $thread) {
            // check for the correct permissions access
            if ($thread->visibleToUser($user)) {
                $filteredThreads[] = $thread; // push onto end of array
            }
	    }
	    $em->flush();
	    
	    $paginator = $this->get('knp_paginator');
	    $pagination = $paginator->paginate(
// 	        $query,
	        $filteredThreads,
	        $this->get('request')->query->get('page', 1) /* page number */,
	        8, /* limit per page */
	        array('distinct' => false)
	    );
	        
	    return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', 
	        array('forum' => $forum,
	            'threads' => $pagination)
	    );
	    
	}
	
	public function editAction(Request $request, Forum $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    $securityContext = $this->get('security.context');
	    
	    // check for edit access using ACL
	    if (false === $securityContext->isGranted('EDIT', $forumid)) {
	        throw new AccessDeniedException();
	    }
	    
	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	     
	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->findOneBy(array('id' => $forumid->getId()));
	    
	    $form = $this->createForm(new ForumFormType(), $forum, array(
	        'user' => $this->getUser(),
	    ));
	    
	    $form->handleRequest($request);
	    
	    if ($form->isValid()) {
	         
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
	                $newforum->addTitleMedia($mediaFile);
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
	            'notice',
	            'Forum edited successfully!'
	        );
	        
	        $forumid = $forum->getId();
	        //             return $this->redirect($this->generateUrl('imdc_forum_list'));
	        return $this->redirect($this->generateUrl('imdc_forum_view_specific', array('forumid' => $forumid)));
        }
	        
        // form not valid, show the basic form
        return $this->render('IMDCTerpTubeBundle:Forum:edit.html.twig',
            array('form' => $form->createView(), 'forum' => $forum
        ));
	}
	
	public function deleteAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	    
	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->findOneBy(array('id' => $forumid));

	    // check if user is the creator of the forum
	    if ($user !== $forum->getCreator()) {
	        $this->get('session')->getFlashBag()->add(
	            'error',
	            'You are not authorized to delete this forum'
	        );
	        
	        return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig',
	            array('forum' => $forum
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
	            'notice',
	            'Forum deleted successfully!'
	        );
	         
	        $forumid = $forum->getId();
	        return $this->redirect($this->generateUrl('imdc_forum_list'));
	    }
	     
	    // form not valid, show the basic form
	    return $this->render('IMDCTerpTubeBundle:Forum:delete.html.twig',
	        array('form' => $form->createView(), 'forum' => $forum
        ));
	}
}