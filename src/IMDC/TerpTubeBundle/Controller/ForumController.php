<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
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
        $securityContext = $this->get('security.context');
        $user = $this->getUser();

		$recentForums = $repo->getRecent($securityContext, $user); //TODO try doctrine criteria
		$forums = $repo->getViewableToUser($securityContext, $user);
		
		$paginator = $this->get('knp_paginator');
		$forums = $paginator->paginate(
            $forums,
			$request->query->get('page', 1), /*page number*/
			8 /*limit per page*/
		);

        //FIXME this seems too costly just for the result of numeric convenience
        $recentForumThreadCount = array();
        $forumThreadCount = array();
        $threadRepo = $em->getRepository('IMDCTerpTubeBundle:Thread');
        foreach ($recentForums as $recentForum) {
            $recentForumThreadCount[] = count($threadRepo->getViewableToUser($securityContext, $recentForum->getId()));
        }
        foreach ($forums as $forum) {
            $forumThreadCount[] = count($threadRepo->getViewableToUser($securityContext, $forum->getId()));
        }

		return $this->render('IMDCTerpTubeBundle:Forum:index.html.twig', array(
            'recentForums' => $recentForums,
			'forums' => $forums,
            'recentForumThreadCount' => $recentForumThreadCount,
            'forumThreadCount' => $forumThreadCount
		));
	}
	
	public function newAction(Request $request, $groupId)
	{
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $forum = new Forum();
        $form = $this->createForm(new ForumFormType(), $forum, array(
            'user' => $user
        ));
        $form->handleRequest($request);

        if (!$form->isValid() && $groupId) {
            $form->get('accessType')->setData($em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_GROUP));

            $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
            if ($group && $group->getUserFounder()->getId() == $user->getId()) {
                $form->get('group')->setData($group);
            }
        } else {
            $currentDateTime = new \DateTime('now');
            $forum->setCreator($user);
            $forum->setLastActivity($currentDateTime);
            $forum->setCreationDate($currentDateTime);

            $media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);*/
                //FIXME override for now. at some point multiple media may be used
                $forum->setTitleMedia($media);
            }

            $user->addForum($forum);

            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            //$aclProvider = $this->get('security.acl.provider');
            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            //$objectIdentity = ObjectIdentity::fromDomainObject($forum);
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            //$acl = $aclProvider->createAcl($objectIdentity);
            $access = $accessProvider->createAccess($objectIdentity);
            //$acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $access->insertEntries($securityIdentity);
            //$aclProvider->updateAcl($acl);
            $accessProvider->updateAccess($access);

            $this->get('session')->getFlashBag()->add(
                'info', 'Forum created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }
        
        return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
	}
	
	public function viewAction(Request $request, $forumid)
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

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('VIEW', $forum) === false) {
            throw new AccessDeniedException();
        }

        $threadRepo = $em->getRepository('IMDCTerpTubeBundle:Thread');

        $recentThreads = $threadRepo->getRecent($securityContext, $forum->getId(), 4);
        $threads = $threadRepo->getViewableToUser($securityContext, $forum->getId());

	    $paginator = $this->get('knp_paginator');
	    $threads = $paginator->paginate(
            $threads,
            $request->query->get('page', 1) /* page number */,
	    	8 /* limit per page */
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

        $em = $this->getDoctrine()->getManager();
	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        if (!$forum) {
            throw new \Exception('forum not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $forum) === false) {
            throw new AccessDeniedException();
        }

	    $form = $this->createForm(new ForumFormType(), $forum);
        $form->handleRequest($request);
	    
	    if ($form->isValid()) {
            $user = $this->getUser();
            $forum->setLastActivity(new \DateTime('now'));

            $media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);*/
                //FIXME override for now. at some point multiple media may be used
                $forum->setTitleMedia($media);
            }

            $em->persist($forum);
	        $em->persist($user);
	        $em->flush();

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // for consistency recreate the underlying acl
            //$accessProvider->deleteAccess($objectIdentity);
            //$access = $accessProvider->createAccess($objectIdentity);

            // get existing underlying acl
            $access = $accessProvider->getAccess($objectIdentity);
            //$access->insertEntries($securityIdentity);
            $access->updateEntries($securityIdentity);
            $accessProvider->updateAccess($access);
	        
	        $this->get('session')->getFlashBag()->add(
                'info', 'Forum edited successfully!'
            );

	        return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }
	        
        return $this->render('IMDCTerpTubeBundle:Forum:edit.html.twig', array(
            'form' => $form->createView(),
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
        $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        if (!$forum) {
            throw new \Exception('forum not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('DELETE', $forum) === false) {
            throw new AccessDeniedException();
        }

	    $form = $this->createForm(new ForumFormDeleteType(), $forum);
	    $form->handleRequest($request);

	    if ($form->isValid()) {
            $user = $this->getUser();
            /*$threads = $forum->getThreads();
            foreach ($threads as $thread) {
                $threadposts = $thread->getPosts();
                foreach ($threadposts as $threadpost) {
                    $threadpost->getAuthor()->removePost($threadpost);
                    $em->remove($threadpost);
                }
                $thread->getCreator()->removeThread($thread);
                $em->remove($thread);
            }*/
	        $user->removeForum($forum);

            $em->remove($forum);
	        $em->persist($user);

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $accessProvider->deleteAccess($objectIdentity);

	        $em->flush();

	        $this->get('session')->getFlashBag()->add(
	            'info', 'Forum deleted successfully!'
	        );

	        return $this->redirect($this->generateUrl('imdc_forum_list'));
	    }

	    return $this->render('IMDCTerpTubeBundle:Forum:delete.html.twig', array(
            'form' => $form->createView(),
            'forum' => $forum
        ));
	}
}
