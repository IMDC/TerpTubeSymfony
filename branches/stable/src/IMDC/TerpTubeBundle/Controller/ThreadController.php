<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use IMDC\TerpTubeBundle\Component\Serializer\Exclusion\UserExclusionStrategy;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use IMDC\TerpTubeBundle\Form\Type\ThreadType;
use IMDC\TerpTubeBundle\Rest\Exception\ThreadException;
use IMDC\TerpTubeBundle\Rest\ThreadResponse;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Thread related actions including new, edit, delete
 *
 * @Rest\NoRoute()
 *
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ThreadController extends FOSRestController implements ClassResourceInterface
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

        if (!$form->isSubmitted()) {
            if ($isNewFromMedia) {
                $form->get('mediaIncluded')->setData(array($media));
            }
        }

        if ($form->isValid()) {
            //TODO both media and title should not be empty

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

            $access = $accessProvider->createAccess($objectIdentity, $form->get('accessType')->get('data'));
            $accessProvider->setSecurityIdentities($objectIdentity, $thread);
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

        // empty post
        $post = new Post();
        $post->setParentThread($thread);

        /** @var UserExclusionStrategy $strategy */
        $strategy = $this->get('imdc_terptube.serializer.exclusion.user_strategy');
        $strategy->checkUser($this->getUser());
        $context = new SerializationContext();
        $context->addExclusionStrategy($strategy);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');
        $userJson = $serializer->serialize($this->getUser(), 'json', $context);

        return $this->render('IMDCTerpTubeBundle:Thread:view.html.twig', array(
            'form' => $form->createView(),
            'thread' => $thread,
            'post' => $post,
            'user_json' => $userJson
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
        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadid);
        if (!$thread) {
            throw new \Exception('thread not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $thread) === false) {
            throw new AccessDeniedException();
        }

        /* @var $accessProvider AccessProvider */
        $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
        $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
        $accessProvider->loadAccessData($objectIdentity);

        $form = $this->createForm(new ThreadType(), $thread, array(
            //'canChooseMedia' => false, //FIXME changing media allowed?
            'access_data' => $accessProvider->getAccessData()
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $thread->setEditedAt($currentDateTime);
            $thread->setEditedBy($user);
            $thread->setMediaDisplayOrder($form->get('mediaIncluded')->getViewData());

            if (count($thread->getMediaIncluded()) > 0) {
                $ordered = $thread->getOrderedMedia();
                $thread->setType($ordered[0]->getType()); // thread type is determined by the first associated media
            }

            $forum = $thread->getParentForum();
            $forum->setLastActivity($currentDateTime);

            $em->persist($thread);
            $em->persist($forum);
            $em->flush();

            // recreate object identity since entity has changed
            $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity, $form->get('accessType')->get('data'));
            $accessProvider->setSecurityIdentities($objectIdentity, $thread);
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
     * @Rest\Route()
     * @Rest\View()
     *
     * @param $threadId
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction($threadId)
    {
        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($threadId);
        if (!$thread) {
            ThreadException::NotFound();
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('DELETE', $thread) === false) {
            ThreadException::AccessDenied();
        }

        $user = $this->getUser();
        $user->removeThread($thread);

        $forum = $thread->getParentForum();
        $forum->setLastActivity(new \DateTime('now'));

        $em->remove($thread);
        $em->persist($forum);
        $em->persist($user);

        /* @var $accessProvider AccessProvider */
        $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
        $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
        $accessProvider->deleteAccess($objectIdentity);

        $em->flush();

        $resp = new ThreadResponse();
        $resp->setRedirectUrl($this->generateUrl('imdc_forum_view', array(
            'forumid' => $forum->getId())));
        return $this->view($resp, 200);
    }
}
