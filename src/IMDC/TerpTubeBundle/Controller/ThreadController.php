<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use IMDC\TerpTubeBundle\Form\Type\ThreadType;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Thread related actions including new, edit, delete
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ThreadController extends Controller
{
    /**
     * @param Request $request
     * @param $forumid
     * @param $mediaId
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function newAction(Request $request, $forumid, $mediaId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $forum = null;
        $media = null;
        if ($forumid) {
            $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($forumid);
        } elseif ($mediaId) {
            $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        }

        if (!$forum && !$media) {
            throw new \Exception('forum/media not found');
        }

        $isNewFromMedia = !$forum;
        $user = $this->getUser();
        $formOptions = array();

        if ($isNewFromMedia) {
            if (!$user->getResourceFiles()->contains($media)) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $formOptions['canChooseForum'] = true;
            $formOptions['user'] = $user;
            $formOptions['em'] = $em;
        }

        $securityContext = $this->get('security.context');

        $thread = new Thread();
        $form = $this->createForm(new ThreadType($securityContext), $thread, $formOptions);
        $form->handleRequest($request);

        if (!$form->isValid() && !$form->isSubmitted()) {
            if ($isNewFromMedia) {
                $form->get('mediaIncluded')->setData(array($media));
            }

            $form->get('accessType')->setData(
                $em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_PUBLIC)
            );
        } else {
            if ($isNewFromMedia) {
                $forum = $form->get('forum')->getData();
            }

            $currentDateTime = new \DateTime('now');
            $thread->setCreator($user);
            $thread->setCreationDate($currentDateTime);
            $thread->setLocked(false);
            $thread->setSticky(false);
            $thread->setLastPostAt($currentDateTime);
            $thread->setParentForum($forum);

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('mediaIncluded')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $thread->setMediaDisplayOrder($form->get('mediaIncluded')->getViewData());

            if (count($thread->getMediaIncluded()) > 0) {
                $ordered = $thread->getOrderedMedia();
                $thread->setType($ordered[0]->getType()); // thread type is determined by the first associated media
            }

            $forum->setLastActivity($currentDateTime);

            $user->addThread($thread);
            $user->increasePostCount(1);

            $em->persist($thread);
            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            /* @var $accessProvider AccessProvider */
            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess();

            $this->get('session')->getFlashBag()->add(
                'success', 'Thread created successfully!'
            );

            return $this->redirect($this->generateUrl('imdc_thread_view', array(
                'threadid' => $thread->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Thread:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function viewAction(Request $request, $threadid)
    {
        // check if the user is logged in
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

        $form = $this->createForm(new PostType(), new Post(), array(
            'canTemporal' => $thread->getType() == 1
        ));
        
        return $this->render('IMDCTerpTubeBundle:Thread:view.html.twig', array(
            'form' => $form->createView(),
            'thread' => $thread
        ));
    }

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(Request $request, $threadid)
    {
        // check if the user is logged in
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

        $form = $this->createForm(new ThreadType(), $thread, array(
            'canChooseMedia' => false //FIXME changing media not allowed?
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $thread->setEditedAt($currentDateTime);
            $thread->setEditedBy($user);

            $forum = $thread->getParentForum();
            $forum->setLastActivity($currentDateTime);

            $em->persist($thread);
            $em->persist($forum);
            $em->flush();

            /* @var $accessProvider AccessProvider */
            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity);
            $access->updateEntries($securityIdentity);
            $accessProvider->updateAccess();

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

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function deleteAction(Request $request, $threadid) //TODO api?
    {
        // check if the user is logged in
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

        $user = $this->getUser();
        $user->removeThread($thread);

        $forum = $thread->getParentForum();
        $forum->setLastActivity(new \DateTime('now'));

        $em->remove($thread);
        $em->persist($forum);
        $em->persist($user);

        $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
        $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
        $accessProvider->deleteAccess($objectIdentity);

        $em->flush();

        $content = array(
            'wasDeleted' => true,
            'redirectUrl' => $this->generateUrl('imdc_forum_view', array(
                'forumid' => $forum->getId()))
        );

        return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
    }
}
