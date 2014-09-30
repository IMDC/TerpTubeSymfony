<?php

namespace IMDC\TerpTubeBundle\Controller;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use FOS\UserBundle\Model\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Form\Type\ThreadFormType;
use IMDC\TerpTubeBundle\Form\Type\ThreadEditFormType;
use IMDC\TerpTubeBundle\Form\Type\ThreadFromMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\PostFormType;
use IMDC\TerpTubeBundle\Form\Type\PostFormFromThreadType;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Permissions;
use IMDC\TerpTubeBundle\IMDCTerpTubeBundle;
use IMDC\TerpTubeBundle\Form\Type\ThreadFormDeleteType;
use IMDC\TerpTubeBundle\Entity\User;
use Symfony\Component\Form\Form;
use IMDC\TerpTubeBundle\Form\Type\AudioMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\VideoMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\OtherMediaFormType;
use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;

/**
 * Controller for all Thread related actions including edit, delete, create
 * 
 * @author paul
 *
 */
class ThreadController extends Controller
{
    /**
     * Lists all threads, currently not useful
     * If a thread with no Permissions object is found, a new ACCESS_CREATOR Permissions object is created
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @deprecated
     */
    public function indexAction() //TODO delete
    {
        $em = $this->getDoctrine()->getManager();
    
        //$threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getMostRecentThreads(30);
        
        $dql = "SELECT t FROM IMDCTerpTubeBundle:Thread t ORDER BY t.creationDate DESC";
        $query = $em->createQuery($dql);
        
        $threads = $query->getResult();
        // if a thread does not have a permissions object yet, we create a new one and
        // make it default private
        foreach ($threads as $thread) {
            if (!$thread->getPermissions()) {
                $thread->setPermissions(new Permissions());
                $thread->getPermissions()->setAccessLevel(Permissions::ACCESS_CREATOR);
                $em->persist($thread);
            }
        }
        $em->flush();
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
//             $query,
            $threads,
            $this->get('request')->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );
        
        /*
        $response = $this->render('IMDCTerpTubeBundle:Thread:index.html.twig',
                array('threads' => $threads)
        );
        */
        
        $response = $this->render('IMDCTerpTubeBundle:Thread:indexpagination.html.twig',
                array('pagination' => $pagination));
        
        return $response;
    }

    public function viewAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadid);

        //TODO permissions
        // check if user has permissions to view thread
        $user = $this->getUser();
        if (!$this->canAccessThread($thread, $user)) {
            $this->get('session')->getFlashBag()->add(
                'danger', 'This topic is private and you are not permitted to view it at this time.'
            );

            return $this->redirect(
                $this->generateUrl('imdc_forum_view', array(
                    'forumid' => $thread->getParentForum()->getId()
                )));
        }

        $postReplyForm = $this->createForm(new PostType(), new Post(), array(
            'canTemporal' => $thread->getType() == 1
        ));
        
        return $this->render('IMDCTerpTubeBundle:_Thread:view.html.twig', array(
            'form' => $postReplyForm->createView(),
            'thread' => $thread,
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
    }
    
    /**
     * If a user is logged in, creates a new thread object under the the given forumid forum
     * @param Request $request the request object
     * @param string $forumid the parent forum's integer id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createNewThreadAction(Request $request, $forumid=null)
    {
    	// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
        
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $newthread = new Thread();
        $form = $this->createForm(new ThreadFormType(), $newthread, array(
            'user' => $this->getUser(),
        ));

        if ($forumid) {
            $forumrepo = $em->getRepository('IMDCTerpTubeBundle:Forum');
            $forum = $forumrepo->findOneBy(array('id' => $forumid));
            $newthread->setParentForum($forum);
        }
        else {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'No valid parent forum found'
            );

            return $this->render('IMDCTerpTubeBundle:_Thread:new.html.twig', array(
                'form' => $form->createView(),
                'forumid' => $forumid,
                'uploadForms' => MyFilesGatewayController::getUploadForms($this)
            ));
        }
        
        // the magic
        $form->handleRequest($request);
        
        if ($form->isValid()) {

            $threadrepo = $em->getRepository('IMDCTerpTubeBundle:Media');
            // if the media text area isn't empty, the user has selected a media
            // file to create a new thread with
            if (!$form->get('mediatextarea')->isEmpty()) {
                
                $rawmediaID = $form->get('mediatextarea')->getData();
                $logger = $this->container->get('logger');
                $logger->info('*************media id is ' . $rawmediaID);
                /** @var $mediaFile IMDC\TerpTubeBundle\Entity\Media */
                $mediaFile = new Media();
                $mediaFile = $threadrepo->findOneBy(array('id' => $rawmediaID));
                
                // check to make sure the user owns this media file
                if ($user->getResourceFiles()->contains($mediaFile)) {
                    $logger = $this->get('logger');
                    $logger->info('User owns this media file');
                    $newthread->addMediaIncluded($mediaFile);
                    $newthread->setType($mediaFile->getType());
                }
                
            }
            
            /*
            // split up the media id by whitespace
            $rawmediaID = trim($form->get('mediaID')->getData());
            try {
                $mediaFile = $threadrepo->findOneBy(array('id' => $rawmediaID));
                $newthread->addMediaIncluded($mediaFile);
            } catch (\PDOException $e) {
                // todo: create message to user about media file not found
                $logger = $this->get('logger');
                $logger->err('Couldnt find media id ' . $rawmediaID);
            }
            */
            /*
            $rawmediaids = explode(' ', $form->get('mediaID')->getData());
            if ($rawmediaids) {
                foreach ($rawmediaids as $possmedia) {
                    try {
                        $mediaFile = $threadrepo->findOneBy(array('id' => $possmedia));
                        $newthread->addMediaIncluded($mediaFile);
                    } catch (\PDOException $e) {
                        // todo: create message to user about media file not found
                    }
                }
            }
            */
           
            $newthread->setCreator($user);
            $newthread->setCreationDate(new \DateTime('now'));
            $newthread->setLocked(FALSE);
            $newthread->setSticky(FALSE);
            $newthread->setLastPostAt(new \DateTime('now'));
            	
            $user->addThread($newthread);
            $user->increasePostCount(1);
            
            // deal with thread permissions
            $this->handleThreadPermissions($newthread, $form);
            
            // request to persist objects to database
            $em->persist($newthread);
            $em->persist($user);
            	
            // persist all objects to database
            $em->flush();
        
            $this->get('session')->getFlashBag()->add(
                    'success',
                    'Thread created successfully!'
            );
            
            $threadInsertedID = $newthread->getId();
            
            return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid' => $threadInsertedID)));
            //return $this->redirect($this->generateUrl('imdc_forum_view', array('forumid' => $newthread->getParentForum()->getId()));
        }
        
        return $this->render('IMDCTerpTubeBundle:_Thread:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
    }
    
    /**
     * This action is called from the My Files section or similar.
     * Given a resource id, creates a new Thread object. Requires the user
     * to select a given forum to create the thread under.
     * @param Request $request
     * @param unknown $resourceid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createNewThreadFromMediaAction(Request $request, $resourceid)
    {
        
    	// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
         
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $newthread = new Thread();
         
        $chosenmedia = $em->getRepository('IMDCTerpTubeBundle:Media')->find($resourceid);
        
        // does the user own the resource?
        // todo: turn this into an access check instead of an ownership check
        if ($chosenmedia->getOwner() !== $user) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'You do not have access to this resource'
            );
            return $this->redirect($this->generateUrl('imdc_myfiles_list'));
        }
        
        //$newthread->setMediaIncluded($chosenmedia);
         
        $form = $this->createForm(new ThreadFromMediaFormType(), $newthread, array(
                'user' => $this->getUser(),
                'resource' => $chosenmedia,
                'em' => $em,
        ));
         
        $form->handleRequest($request);
         
        if ($form->isValid()) {
        
            $newthread->setCreator($user);
            $newthread->setCreationDate(new \DateTime('now'));
            $newthread->setLocked(FALSE);
            $newthread->setSticky(FALSE);
            $newthread->setLastPostAt(new \DateTime('now'));
            $newthread->setType($chosenmedia->getType());
        
            $user->addThread($newthread);
        
            // request to persist object to database
            $em->persist($newthread);
            $em->persist($user);
        
            // persist all objects to database
            $em->flush();
             
            $this->get('session')->getFlashBag()->add(
                    'info',
                    'Thread created successfully!'
            );
            return $this->redirect($this->generateUrl('imdc_forum_view', array('forumid' => $newthread->getParentForum()->getId())));
        }

        return $this->render('IMDCTerpTubeBundle:_Thread:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this),
            'isNewFromMedia' => true,
            'mediaFile' => $chosenmedia
        ));
    }
    
    public function editThreadAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
         
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
         
        $threadToEdit = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
         
        // does the user own this thread?
        if (!$user->getThreads()->contains($threadToEdit)) {
            $this->get('session')->getFlashBag()->add(
                    'danger',
                    'You do not have permission to edit this thread'
            );
            return $this->redirect($this->generateUrl('imdc_index'));
        }
         
        $threadeditform = $this->createForm(new ThreadEditFormType(), $threadToEdit,
                array('user' => $user,
                      'thread' => $threadToEdit,
                ));
         
        $threadeditform->handleRequest($request);
         
        if ($threadeditform->isValid()) {
             
            $threadToEdit->setEditedAt(new \DateTime('now'));
            $threadToEdit->setEditedBy($user);
             
//             $mediarepo = $em->getRepository('IMDCTerpTubeBundle:Media');
//             if (!$threadeditform->get('mediatextarea')->isEmpty()) {
                 
//                 $rawmediaID = $threadeditform->get('mediatextarea')->getData();
//                 $logger = $this->container->get('logger');
//                 $logger->info('*************media id is ' . $rawmediaID);
//                 /** @var $mediaFile \IMDCTerpTubeBundle:Media */
//                 $mediaFile = $mediarepo->findOneBy(array('id' => $rawmediaID));
                 
//                 if ($user->getResourceFiles()->contains($mediaFile)) {
//                     $logger = $this->get('logger');
//                     $logger->info('User owns this media file');
//                     //FIXME: setMediaIncluded replaces ALL media when editing a thread
//                     $threadToEdit->setMediaIncluded($mediaFile);
//                 }
                 
//             }
             
            //$this->handleThreadPermissions($threadToEdit, $threadeditform);
            
            // deal with thread permissions
            $threadAccessLevel = $threadToEdit->getPermissions()->getAccessLevel();
            
            if ($threadAccessLevel == Permissions::ACCESS_CREATORS_FRIENDS) {
                $threadToEdit->getPermissions()->setUsersWithAccess($user->getFriendsList());
                $threadToEdit->getPermissions()->setUserGroupsWithAccess(null);
            }
            else if ($threadAccessLevel == Permissions::ACCESS_USER_LIST) {
                $threadToEdit->getPermissions()->setUsersWithAccess(null); // reset access list
                $usermanager = $this->get('fos_user.user_manager');
                $rawusers = explode(',', $threadeditform->get('permissions')->get('usersWithAccess')->getData());
                if ($rawusers) {
                    $logger = $this->container->get('logger');
                    $theusers = new ArrayCollection();
                    foreach ($rawusers as $possuser) {
                        try {
                            $user = $usermanager->findUserByUsername($possuser);
                            $threadToEdit->getPermissions()->addUsersWithAccess($user);
                        } catch (Exception $e) {
                            //FIXME: do something to notify user that user name not found?
                            $logger->info("\n\n*****ERROR: username: " . $possuser . "not found in USER_LIST****\n\n");
                        }
                    }
                }
                else {
                    $threadToEdit->getPermissions()->setUsersWithAccess(null);
                }
                $threadToEdit->getPermissions()->setUserGroupsWithAccess(null);
            }
            else if ($threadAccessLevel == Permissions::ACCESS_GROUP_LIST) {
                // user groups with access set automagically
                $threadToEdit->getPermissions()->setUsersWithAccess(null);
            }
            else {
                // permissions that get here are
                // access_creator, access_public, access_with_link, registered_members
                // really do this?
                $threadToEdit->getPermissions()->setUserGroupsWithAccess(null);
                $threadToEdit->getPermissions()->setUsersWithAccess(null);
            }
            
            // request to persist objects to database
            //$em->persist($user);
            $em->persist($threadToEdit);
            $em->persist($threadToEdit->getPermissions());
            $em->flush();
             
            $this->get('session')->getFlashBag()->add(
                    'success',
                    'Forum post edited successfully!'
            );
             
            return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)));
        }
         
        return $this->render('IMDCTerpTubeBundle:_Thread:edit.html.twig', array(
            'form' => $threadeditform->createView(),
            'thread' => $threadToEdit
        ));
    }
    
    
    public function deleteThreadAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        // check if the user owns this thread
        $threadToDelete = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
        $parentForum = $threadToDelete->getParentForum();
        
        if ($user->getThreads()->contains($threadToDelete) && $threadToDelete->getCreator() === $user) {
            
            // create the thread delete form
            $threadDeleteForm = $this->createForm(new ThreadFormDeleteType(), $threadToDelete);
             
            $threadDeleteForm->handleRequest($request);
             
            if ($threadDeleteForm->isValid()) {
            
                $em->remove($threadToDelete);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Post successfully deleted!'
                );
                
                if ($parentForum) {
                    return $this->redirect($this->generateUrl('imdc_forum_view', array('forumid'=>$parentForum->getId())));
                }
                else {
                    return $this->redirect($this->generateUrl('imdc_forum_list'));
                }
            }
            
            return $this->render('IMDCTerpTubeBundle:_Thread:delete.html.twig', array(
                'form' => $threadDeleteForm->createView(),
                'thread' => $threadToDelete
            ));
            
        }
        else { // user doesn't own this thread to delete it
            $this->get('session')->getFlashBag()->add(
                'danger',
                'You do not have permission to delete this post'
            );
            return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)));
        }
        
    }
    
    /**
     * Determines if the given user has permission access to view the given thread,
     * Also creates a new default permissions object for threads without one
     * 
     * @param IMDCTerpTubeBundle:Thread $thread
     * @param IMDCTerpTubeBundle:User $user
     * @return boolean
     */
    public function canAccessThread($thread, $user)
    {
        // check for a thread permissions object
        $accessGranted = FALSE;
        $threadperms = $thread->getPermissions();
        if ($threadperms){
            switch ($threadperms->getAccessLevel()) {
            	case Permissions::ACCESS_CREATOR:
            	    if ($user == $thread->getCreator()) {
            	        $accessGranted = TRUE;
            	    }
            	    break;
            	     
            	case Permissions::ACCESS_CREATORS_FRIENDS:
            	    if ($thread->getCreator->getFriendsList()->contains($user) || $user == $thread->getCreator() ) {
            	        $accessGranted = TRUE;
            	    }
            	    break;
            	     
            	case Permissions::ACCESS_GROUP_LIST:
            	    $possibleGroups = array_intersect($user->getUserGroups()->toArray(), $threadperms->getUserGroupsWithAccess()->toArray());
            	    if (!empty($possibleGroups) || $user == $thread->getCreator() ) {
            	        $accessGranted = TRUE;
            	    }
            	    break;
            	     
            	case Permissions::ACCESS_USER_LIST:
            	    if ($threadperms->getUsersWithAccess()->contains($user) || $user == $thread->getCreator()) {
            	        $accessGranted = TRUE;
            	    }
            	    break;
            	     
            	case Permissions::ACCESS_WITH_LINK:
            	case Permissions::ACCESS_PUBLIC:
            	    // do something common for both with link and accesspublic
            	    $accessGranted = TRUE;
            	    break;
        
            	default:
            	    $accessGranted = FALSE;
            	    break;
            }
        }
        else { // no thread permissions exist, create default private permissions
            $perms = new Permissions();
            $perms->setAccessLevel(Permissions::ACCESS_CREATOR);
            $thread->setPermissions($perms);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->persist($perms);
            $em->flush();
            
            $this->get('logger')->info("\n\n*********new Permissions object created for thread: " . $thread->getId());
            if ($thread->getCreator() === $user) { // user is not the creator of this thread which is now private
                $accessGranted = TRUE;
            }
        }
        
        return $accessGranted;
    }
    
    protected function handleThreadPermissions(Thread $thread, Form $form)
    {
        // deal with thread permissions
        $threadAccessLevel = $thread->getPermissions()->getAccessLevel();
        
        if ($threadAccessLevel == Permissions::ACCESS_CREATORS_FRIENDS) {
            $thread->getPermissions()->setUsersWithAccess($this->getUser()->getFriendsList());
            $thread->getPermissions()->setUserGroupsWithAccess(null);
        }
        else if ($threadAccessLevel == Permissions::ACCESS_USER_LIST) {
        
            $usermanager = $this->get('fos_user.user_manager');
            $rawusers = explode(',', $form->get('permissions')->get('usersWithAccess')->getData());
            if ($rawusers) {
                $logger = $this->container->get('logger');
                $theusers = new ArrayCollection();
                foreach ($rawusers as $possuser) {
                    try {
                        $user = $usermanager->findUserByUsername($possuser);
                        $thread->getPermissions()->addUsersWithAccess($user);
                    } catch (\PDOException $e) {
                        //FIXME: do something to notify user that user name not found?
                        $logger->info("\n\n*****ERROR: username: " . $possuser . "not found in USER_LIST****\n\n");
                    }
                }
            }
            else {
                $thread->getPermissions()->setUsersWithAccess(null);
            }
            $thread->getPermissions()->setUserGroupsWithAccess(null);
        }
        else if ($threadAccessLevel == Permissions::ACCESS_GROUP_LIST) {
            // user groups with access set automagically
            $thread->getPermissions()->setUsersWithAccess(null);
        }
        else {
            // permissions that get here are
            // access_creator, access_public, access_with_link, registered_members
            // really do this?
            $thread->getPermissions()->setUserGroupsWithAccess(null);
            $thread->getPermissions()->setUsersWithAccess(null);
        }
	}

    
}