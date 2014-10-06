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
    public function newAction(Request $request, $forumid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        if (!$forum) {
            throw new \Exception('forum not found');
        }

        $thread = new Thread();
        $form = $this->createForm(new ThreadFormType(), $thread, array(
            'em' => $em
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $thread->setCreator($user);
            $thread->setCreationDate($currentDateTime);
            $thread->setLocked(false);
            $thread->setSticky(false);
            $thread->setLastPostAt($currentDateTime);
            $thread->setParentForum($forum);

            $media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$thread->getMediaIncluded()->contains($media)) {
                    $thread->addMediaIncluded($media);
                    $thread->setType($media->getType());
                }
            }

            $forum->setLastActivity($currentDateTime);

            $user->addThread($thread);
            $user->increasePostCount(1);

            $em->persist($thread);
            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess($access);

            $this->get('session')->getFlashBag()->add(
                'success', 'Thread created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_thread_view', array(
                'threadid' => $thread->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Thread:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
    }

    public function viewAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
        if (!$thread) {
            throw new \Exception('thread not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('VIEW', $thread) === false) {
            throw new AccessDeniedException();
        }

        $postForm = $this->createForm(new PostType(), new Post(), array(
            'canTemporal' => $thread->getType() == 1
        ));
        
        return $this->render('IMDCTerpTubeBundle:Thread:view.html.twig', array(
            'form' => $postForm->createView(),
            'thread' => $thread,
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

        return $this->render('IMDCTerpTubeBundle:Thread:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this),
            'isNewFromMedia' => true,
            'mediaFile' => $chosenmedia
        ));
    }
    
    public function editAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
        if (!$thread) {
            throw new \Exception('thread not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $thread) === false) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ThreadFormType(), $thread, array(
            'em' => $em,
            'canChooseMedia' => false
        ));
        $form->handleRequest($request);

        if ($thread->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $thread->setEditedAt($currentDateTime);
            $thread->setEditedBy($user);


            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$thread->getMediaIncluded()->contains($media)) {
                    $thread->addMediaIncluded($media);
                    $thread->setType($media->getType());
                }
            }*/

            $forum = $thread->getParentForum();
            $forum->setLastActivity($currentDateTime);

            $em->persist($thread);
            $em->persist($forum);
            $em->flush();

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // for consistency recreate the underlying acl
            $accessProvider->deleteAccess($objectIdentity);
            $access = $accessProvider->createAccess($objectIdentity);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess($access);

            $this->get('session')->getFlashBag()->add(
                'success', 'Forum post edited successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_thread_view', array(
                'threadid' => $thread->getId()
            )));
        }
         
        return $this->render('IMDCTerpTubeBundle:Thread:edit.html.twig', array(
            'form' => $form->createView(),
            'thread' => $thread
        ));
    }
    
    
    public function deleteAction(Request $request, $threadid)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
        if (!$thread) {
            throw new \Exception('thread not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('DELETE', $thread) === false) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ThreadFormDeleteType(), $thread);
        $form->handleRequest($request);
             
        if ($form->isValid()) {
            $user = $this->getUser();
            $user->removeThread($thread);

            $forum = $thread->getParentForum();
            $forum->setLastActivity(new \DateTime('now'));

            $em->remove($thread);
            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success', 'Post successfully deleted!'
            );

            return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Thread:delete.html.twig', array(
            'form' => $form->createView(),
            'thread' => $thread
        ));
    }
}
