<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\ForumRepository;
use IMDC\TerpTubeBundle\Form\Type\ForumType;
use IMDC\TerpTubeBundle\Form\Type\ForumFormDeleteType;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Model\JSEntities;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Forum object related actions such as new, edit, delete
 * @author paul
 *
 */
class ForumController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
	{
        /**
         * @var $repo ForumRepository
         */

		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		$em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IMDCTerpTubeBundle:Forum');
        $user = $this->getUser();
        $securityContext = $this->get('security.context');

		$sortParams = array(
            'sort' => $request->query->get('sort', 'f.lastActivity'),
            'direction' => $request->query->get('direction', 'desc')
        );

        $paginator = $this->get('knp_paginator');
        $forums = $paginator->paginate(
            $repo->getViewableToUser($user, $securityContext, $sortParams),
			$request->query->get('page', 1), /*page number*/
			8 /*limit per page*/
		);

        foreach ($sortParams as $key => $value) {
            $forums->setParam($key, $value);
        }

		return $this->render('IMDCTerpTubeBundle:Forum:index.html.twig', array(
            'forums' => $forums,
            'forumThreadCount' => $em->getRepository('IMDCTerpTubeBundle:Thread')
                    ->getViewableCountForForums($forums, $securityContext)
		));
	}

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function newAction(Request $request, $groupId)
	{
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($groupId) {
            $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        }

        $forum = new Forum();
        $form = $this->createForm(new ForumType(), $forum, array(
            'user' => $user,
            'group' => $group
        ));
        $form->handleRequest($request);

        if (!$form->isValid() && !$form->isSubmitted()) {
            if ($group) {
                $form->get('accessType')->setData($em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_GROUP));

                if ($group->getUserFounder()->getId() == $user->getId() || $group->getMembersCanAddForums()) {
                    $form->get('group')->setData($group);
                }
            } else {
                $form->get('accessType')->setData($em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_PUBLIC));
            }
        } else {
            $currentDateTime = new \DateTime('now');
            $forum->setCreator($user);
            $forum->setLastActivity($currentDateTime);
            $forum->setCreationDate($currentDateTime);

            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);*
                //FIXME override for now. at some point multiple media may be used
                $forum->setTitleMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('titleMedia')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $forum->setMediaDisplayOrder($form->get('titleMedia')->getViewData());

            $user->addForum($forum);

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
                'info', 'Forum created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()
            )));
        }
        
        return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig', array(
            'form' => $form->createView()
        ));
	}

    /**
     * @param Request $request
     * @param $forumid
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
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
        $sortParams = array(
            'sort' => $request->query->get('sort', 't.lastPostAt'),
            'direction' => $request->query->get('direction', 'desc')
        );

        $paginator = $this->get('knp_paginator');
	    $threads = $paginator->paginate(
            $threadRepo->getViewableToUser($forum->getId(), $securityContext, $sortParams),
            $request->query->get('page', 1) /* page number */,
	    	8 /* limit per page */
	    );

        foreach ($sortParams as $key => $value) {
            $threads->setParam($key, $value);
        }

        $ordered = Utils::orderMedia($forum->getTitleMedia(), $forum->getMediaDisplayOrder());
        $mediaJson = array();
        foreach ($ordered as $media) {
            $mediaJson[] = JSEntities::getMediaObject($media);
        }

	    return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
	    	'forum' => $forum,
            'mediaJson' => $mediaJson,
	    	'threads' => $threads
	    ));
	}

    /**
     * @param Request $request
     * @param $forumid
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function editAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        if (!$forum) {
            throw new \Exception('forum not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $forum) === false) {
            throw new AccessDeniedException();
        }

	    $form = $this->createForm(new ForumType(), $forum, array(
            'user' => $user
        ));
        $form->handleRequest($request);

        if (!$form->isValid()) {
            /*if (count($forum->getTitleMedia()) > 0) {
                $form->get('mediatextarea')->setData($forum->getTitleMedia()->get(0));
            }*/

            /*$ordered = array();
            foreach ($forum->getTitleMedia() as $media) {
                foreach ($forum->getMediaDisplayOrder() as $index => $mediaId) {
                    if ($media->getId() == $mediaId) {
                        $ordered[$index] = $media;
                    }
                }
            }
            ksort($ordered);*/

            $form->get('titleMedia')->setData(
                Utils::orderMedia(
                    $forum->getTitleMedia(),
                    $forum->getMediaDisplayOrder()));
        } else {
            $forum->setLastActivity(new \DateTime('now'));

            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$forum->getTitleMedia()->contains($media))
                    $forum->addTitleMedia($media);*
                //FIXME override for now. at some point multiple media may be used
                $forum->setTitleMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('titleMedia')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $forum->setMediaDisplayOrder($form->get('titleMedia')->getViewData());

            if ($forum->getAccessType()->getId() !== AccessType::TYPE_GROUP) {
                $forum->setGroup(null);
            }

            $em->persist($forum);
	        $em->persist($user);
	        $em->flush();

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->getAccess($objectIdentity);
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
            'forum' => $forum
        ));
	}

    /**
     * @param Request $request
     * @param $forumid
     * @return RedirectResponse|Response
     * @throws BadRequestHttpException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function deleteAction(Request $request, $forumid)
	{
        // if not ajax, throw an error
        if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }

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

        $content = array(
            'wasDeleted' => true,
            'redirectUrl' => $this->generateUrl('imdc_forum_list')
        );

        return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
	}
}
