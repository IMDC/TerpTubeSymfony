<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Form\Type\ForumType;
use IMDC\TerpTubeBundle\Rest\Exception\ForumException;
use IMDC\TerpTubeBundle\Rest\ForumResponse;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Forum object related actions such as new, edit, delete
 *
 * @Rest\NoRoute()
 *
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ForumController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
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
     */
    public function newAction(Request $request, $groupId)
    {
        $em = $this->getDoctrine()->getManager();

        $group = null;
        if ($groupId) {
            $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        }

        $securityContext = $this->get('security.context');
        $user = $this->getUser();
        if ($group) {
            if ($securityContext->isGranted('EDIT', $group) === false &&
                ($group->getMembers()->contains($user) && $group->getMembersCanAddForums()) === false
            ) {
                throw new AccessDeniedException('user cannot create group associated forums');
            }
        } else {
            if ($securityContext->isGranted('ROLE_CREATE_FORUMS') === false) {
                throw new AccessDeniedException('user cannot create top level forums');
            }
        }

        $forum = new Forum();
        $form = $this->createForm(new ForumType(), $forum, array(
            'user' => $user,
            'group' => $group
        ));
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            if ($group) {
                $form->get('accessType')->setData(
                    $em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_GROUP)
                );

                if ($group->getUserFounder()->getId() == $user->getId() || $group->getMembersCanAddForums()) {
                    $form->get('group')->setData($group);
                }
            }
        }

        if ($form->isValid()) {
            $currentDateTime = new \DateTime('now');
            $forum->setCreator($user);
            $forum->setLastActivity($currentDateTime);
            $forum->setCreationDate($currentDateTime);
            $forum->setMediaDisplayOrder($form->get('titleMedia')->getViewData());

            $user->addForum($forum);

            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            /* @var $accessProvider AccessProvider */
            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity, $form->get('accessType')->get('data'));
            $accessProvider->setSecurityIdentities($objectIdentity, $forum);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess();

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
     * @throws \Exception
     */
    public function viewAction(Request $request, $forumid)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        if (!$forum) {
            throw new \Exception('forum not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('VIEW', $forum) === false) {
            //if the suer is not allowed to see this content, redirect them back to forum list
            return $this->redirect($this->generateUrl('imdc_forum_list'));
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

        return $this->render('IMDCTerpTubeBundle:Forum:view.html.twig', array(
            'forum' => $forum,
            'threads' => $threads
        ));
    }

    /**
     * @param Request $request
     * @param $forumid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(Request $request, $forumid)
    {
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

        /* @var $accessProvider AccessProvider */
        $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
        $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
        $accessProvider->loadAccessData($objectIdentity);

        $form = $this->createForm(new ForumType(), $forum, array(
            'user' => $user,
            'access_data' => $accessProvider->getAccessData()
        ));
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $form->get('titleMedia')->setData($forum->getOrderedMedia());
        }

        if ($form->isValid()) {
            $forum->setLastActivity(new \DateTime('now'));
            $forum->setMediaDisplayOrder($form->get('titleMedia')->getViewData());

            if ($forum->getAccessType()->getId() !== AccessType::TYPE_GROUP) {
                $forum->setGroup(null);
            }

            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            // recreate object identity since entity has changed
            $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity, $form->get('accessType')->get('data'));
            $accessProvider->setSecurityIdentities($objectIdentity, $forum);
            $access->updateEntries($securityIdentity);
            $accessProvider->updateAccess();

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
     * @Rest\Route()
     * @Rest\View()
     *
     * @param $forumId
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction($forumId)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumId);
        if (!$forum) {
            ForumException::NotFound();
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('DELETE', $forum) === false) {
            ForumException::AccessDenied();
        }

        $user = $this->getUser();
        $user->removeForum($forum);

        $em->remove($forum);
        $em->persist($user);

        /* @var $accessProvider AccessProvider */
        $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
        $objectIdentity = AccessObjectIdentity::fromAccessObject($forum);
        $accessProvider->deleteAccess($objectIdentity);

        $em->flush();

        $resp = new ForumResponse();
        $resp->setRedirectUrl($this->generateUrl('imdc_forum_list'));
        return $this->view($resp, 200);
    }
}
