<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Post related actions including creating, deleting, editing and replying
 * @package IMDC\TerpTubeBundle\Controller
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class PostController extends Controller
{
    /**
     * @param Request $request
     * @param $threadId
     * @param $pid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function newAction(Request $request, $threadId, $pid) //TODO api?
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $thread = null;
        $postParent = null;
        if ($threadId) {
            $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadId);
        }
        if ($pid) {
            $postParent = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        }

        if (!$thread && !$postParent) {
            throw new \Exception('thread/post not found');
        }

        $isPostReply = !!$postParent;
        $post = new Post();
        $form = $this->createForm(new PostType(), $post, array(
            'canTemporal' => !$isPostReply ? ($thread->getType() == 1) : false
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $post->setAuthor($user);
            $post->setCreated($currentDateTime);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('attachedFile')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $post->setMediaDisplayOrder($form->get('attachedFile')->getViewData());

            if (!$isPostReply) {
                $post->setParentThread($thread);
            } else {
                $post->setParentPost($postParent);
                $post->setParentThread($postParent->getParentThread());
            }

            $em->persist($post);
            $em->flush();

            if ($isPostReply && !$thread)
                $thread = $postParent->getParentThread();

            $thread->setLastPostAt($currentDateTime);
            $thread->setLastPostID($post->getId());

            $forum = $thread->getParentForum();
            $forum->setLastActivity($currentDateTime);

            $user->addPost($post);

            $em->persist($post);
            $em->persist($thread);
            if ($postParent)
                $em->persist($postParent);
            $em->persist($forum);
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success', 'Reply created successfully!'
            );

            $serializer = $this->get('jms_serializer');
            $content = array(
                'wasReplied' => true,
                'post' => json_decode($serializer->serialize($post, 'json'), true),
                'redirectUrl' => $this->generateUrl('imdc_thread_view', array(
                    'threadid' => $thread->getId()))
            );
        } else {
            $content = array(
                'wasReplied' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.reply.html.twig', array(
                    'form' => $form->createView(),
                    'post' => $postParent, 
                	'thread' =>$thread))
            );
        }

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param Request $request
     * @param $pid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function viewAction(Request $request, $pid) //TODO api?
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        if (!$post) {
            throw new \Exception('post not found');
        }

        $content = array(
            'html' => $this->renderView('IMDCTerpTubeBundle:Post:view.html.twig', array(
                'post' => $post))
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param Request $request
     * @param $pid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(Request $request, $pid) //TODO api?
	{
        // check if the user is logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();
		$post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        if (!$post) {
            throw new \Exception('post not found');
        }

        $user = $this->getUser();
		if (!$post->getAuthor() == $user) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new PostType(), $post, array(
            'canTemporal' => !$post->getParentPost() ? ($post->getParentThread()->getType() == 1) : false
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $post->setEditedAt(new \DateTime('now'));
            $post->setEditedBy($user);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('attachedFile')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $post->setMediaDisplayOrder($form->get('attachedFile')->getViewData());

            $forum = $post->getParentThread()->getParentForum();
            $forum->setLastActivity(new \DateTime('now'));

            $em->persist($post);
            $em->persist($forum);
            $em->flush();

            $serializer = $this->get('jms_serializer');
            $content = array(
                'wasEdited' => true,
                'post' => json_decode($serializer->serialize($post, 'json'), true),
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:view.html.twig', array(
                    'post' => $post))
            );
        } else {
            $content = array(
                'wasEdited' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.edit.html.twig', array(
                    'form' => $form->createView(),
                    'post' => $post))
            );
        }

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
	}

    /**
     * @param Request $request
     * @param $pid
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function deleteAction(Request $request, $pid) //TODO api?
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        if (!$post) {
            throw new \Exception('post not found');
        }

        $user = $this->getUser();
        if (!$post->getAuthor() == $user) {
            throw new AccessDeniedException();
        }

        $user->removePost($post);
        $user->decreasePostCount(1);

        $em->persist($user);
        $em->remove($post);
        $em->flush();

        $content = array(
            'wasDeleted' => true
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}
