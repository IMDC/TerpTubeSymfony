<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
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
class PostController extends FOSRestController
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
            'canTemporal' => !$isPostReply ? ($thread->getType() == 1) : false,
            'is_post_reply' => $isPostReply
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $post->setAuthor($user);
            $post->setCreated($currentDateTime);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));
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

//             $this->get('session')->getFlashBag()->add(
//                 'success', 'Reply created successfully!'
//             );
            

            $serializer = $this->get('jms_serializer');
            $content = array(
                'wasReplied' => true,
                'post' => json_decode($serializer->serialize($post, 'json'), true),
                'redirectUrl' => $this->generateUrl('imdc_thread_view', array(
                    'threadid' => $thread->getId())),
            	'html' => $this->renderView('IMDCTerpTubeBundle:Post:view.html.twig', array(
            			'post' => $post,
            			'is_post_reply' => $isPostReply))
            );
        } else {
            $post->setId(-rand());
            $post->setParentThread($thread);
            $post->setParentPost($postParent);

            $content = array(
                'wasReplied' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.reply.html.twig', array(
                    'form' => $form->createView(),
                    'post' => $post,
                    'is_post_reply' => $isPostReply))
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
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        if (!$post) {
            throw new \Exception('post not found');
        }

        $content = array(
            'html' => $this->renderView('IMDCTerpTubeBundle:Post:view.html.twig', array(
                'post' => $post,
                'is_post_reply' => !!$post->getParentPost()))
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

        $isPostReply = !!$post->getParentPost();

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

            $serializer = $this->get('jms_serializer');
            $content = array(
                'wasEdited' => true,
                'post' => json_decode($serializer->serialize($post, 'json'), true),
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:view.html.twig', array(
                    'post' => $post,
                    'is_post_reply' => $isPostReply))
            );
        } else {
            $content = array(
                'wasEdited' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.edit.html.twig', array(
                    'form' => $form->createView(),
                    'post' => $post,
                    'is_post_reply' => $isPostReply))
            );
        }

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
	}

    /**
     * @Rest\View()
     *
     * @param Request $request
     * @param $postId
     * @return \FOS\RestBundle\View\View|RedirectResponse
     */
    public function deleteAction(Request $request, $postId)
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
            'message' => 'deleted',
            'wasDeleted' => true //TODO drop wasWhatevers
        )), 200);
    }
}
