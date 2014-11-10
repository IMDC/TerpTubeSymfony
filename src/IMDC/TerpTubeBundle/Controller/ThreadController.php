<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Form\Type\ThreadFormType;
use IMDC\TerpTubeBundle\Form\Type\ThreadFormDeleteType;

/**
 * Controller for all Thread related actions including edit, delete, create
 * 
 * @author paul
 *
 */
class ThreadController extends Controller
{
    /**
     * @param Request $request
     * @param $forumid
     * @param $mediaId
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function newAction(Request $request, $forumid, $mediaId)
    {
        // check if user logged in
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

        $thread = new Thread();
        $securityContext = $this->get('security.context');
        $form = $this->createForm(new ThreadFormType($securityContext), $thread, $formOptions);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            if ($isNewFromMedia) {
                $form->get('mediatextarea')->setData($media);
            }

            $form->get('accessType')->setData($em->getRepository('IMDCTerpTubeBundle:AccessType')->find(AccessType::TYPE_PUBLIC));
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

            $media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$thread->getMediaIncluded()->contains($media)) {
                    $thread->addMediaIncluded($media);
                    $thread->setType($media->getType());
                }*/
                //FIXME override for now. at some point multiple media may be used
                $thread->setMediaIncluded($media);
                $thread->setType($media->getType());
            }

            $forum->setLastActivity($currentDateTime);

            $user->addThread($thread);
            $user->increasePostCount(1);

            $em->persist($thread);
            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            $accessProvider = $this->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($thread);
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

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
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

        $form = $this->createForm(new PostType(), new Post(), array(
            'canTemporal' => $thread->getType() == 1
        ));
        
        return $this->render('IMDCTerpTubeBundle:Thread:view.html.twig', array(
            'form' => $form->createView(),
            'thread' => $thread,
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
    }

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
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
            'canChooseMedia' => false //FIXME changing media not allowed?
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $thread->setEditedAt($currentDateTime);
            $thread->setEditedBy($user);

            //FIXME changing media not allowed?
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
            //$accessProvider->deleteAccess($objectIdentity);
            //$access = $accessProvider->createAccess($objectIdentity);

            // get existing underlying acl
            $access = $accessProvider->getAccess($objectIdentity);
            //$access->insertEntries($securityIdentity);
            $access->updateEntries($securityIdentity);
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

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
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
