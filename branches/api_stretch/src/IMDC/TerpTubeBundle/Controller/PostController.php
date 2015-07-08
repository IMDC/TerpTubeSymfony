<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Form\Type\PostType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Post related actions including creating, deleting, editing and replying
 *
 * @Rest\View()
 *
 * @package IMDC\TerpTubeBundle\Controller
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class PostController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\Post() //TODO api?. decouple rest new/post
     * @Rest\QueryParam(name="threadId", requirements="\d+")
     * @Rest\QueryParam(name="parentPostId", requirements="\d+")
     *
     * @param Request $request
     * @param ParamFetcher $paramFetcher
     * @return \FOS\RestBundle\View\View
     */
    public function newAction(Request $request, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $threadId = $paramFetcher->get('threadId');
        $parentPostId = $paramFetcher->get('parentPostId');
        $thread = null;
        $postParent = null;
        if ($threadId) {
            $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadId);
        }
        if ($parentPostId) {
            $postParent = $em->getRepository('IMDCTerpTubeBundle:Post')->find($parentPostId);
        }

        if (!$thread && !$postParent) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'thread/post not found'
            )), 500); //TODO decide status code
        }

        $post = new Post();
        $post->setParentThread($thread);
        $post->setParentPost($postParent);
        $form = $this->createForm(new PostType(), $post, array(
            'canTemporal' => (!$post->isPostReply() && $thread->getType() == 1),
            'is_post_reply' => $post->isPostReply()
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $post->setAuthor($user);
            $post->setCreated($currentDateTime);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));
            $post->setMediaDisplayOrder($form->get('attachedFile')->getViewData());

            if (!$post->isPostReply()) {
                $post->setParentThread($thread);
            } else {
                $post->setParentPost($postParent);
                $post->setParentThread($postParent->getParentThread());
            }

            $em->persist($post);
            $em->flush();

            if ($post->isPostReply() && !$thread)
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

            return $this->view(array('post' => $post), 200);
        }

        return $this->view(array(
            'post' => $post,
            'form' => $this->renderView('IMDCTerpTubeBundle:Post:form.new.html.twig', array(
                'form' => $form->createView(),
                'post' => $post))
        ), 200);
    }

    /**
     * @param $postId
     * @return \FOS\RestBundle\View\View
     */
    public function getAction($postId)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($postId);
        if (!$post) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'post not found'
            )), 500); //TODO decide status code
        }

        return $this->view(array('post' => $post), 200);
    }

    /**
     * @Rest\Post() //TODO api?. decouple rest edit/put
     *
     * @param Request $request
     * @param $postId
     * @return \FOS\RestBundle\View\View
     */
    public function editAction(Request $request, $postId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Post $post */
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($postId);
        if (!$post) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'post not found'
            )), 500); //TODO decide status code
        }

        $user = $this->getUser();
        if (!$post->getAuthor() == $user) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new PostType(), $post, array(
            'canTemporal' => (!$post->getParentPost() && $post->getParentThread()->getType() == 1)
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $post->setEditedAt(new \DateTime('now'));
            $post->setEditedBy($user);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));
            $post->setMediaDisplayOrder($form->get('attachedFile')->getViewData());

            $forum = $post->getParentThread()->getParentForum();
            $forum->setLastActivity(new \DateTime('now'));

            $em->persist($post);
            $em->persist($forum);
            $em->flush();

            return $this->view(array('post' => $post), 200);
        }

        //TODO form errors

        return $this->view(array(
            'post' => $post,
            'form' => $this->renderView('IMDCTerpTubeBundle:Post:form.edit.html.twig', array(
                'form' => $form->createView(),
                'post' => $post))
        ), 200);
    }

    /**
     * @param $postId
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction($postId)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($postId);
        if (!$post) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'post not found'
            )), 500); //TODO decide status code
        }

        $user = $this->getUser();
        if (!$post->getAuthor() == $user) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'access denied'
            )), 500); //TODO decide status code
        }

        $user->removePost($post);
        $user->decreasePostCount(1);

        $em->persist($user);
        $em->remove($post);
        $em->flush();

        return $this->view(array('status' => array(
            'code' => 0,
            'message' => 'deleted'
        )), 200);
    }
}