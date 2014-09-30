<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Form\Type\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use FOS\UserBundle\Model\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Form\Type\PostFormType;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use IMDC\TerpTubeBundle\Form\Type\PostEditFormType;
use IMDC\TerpTubeBundle\Form\Type\PostReplyToPostFormType;
use IMDC\TerpTubeBundle\Form\Type\PostFormFromThreadType;
use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for all Post related actions including creating, deleting, editing and replying
 * @author paul
 *
 */
class PostController extends Controller
{
    
	public function indexAction() //TODO delete
	{
		$em = $this->getDoctrine()->getManager();
		
		$posts = $em->getRepository('IMDCTerpTubeBundle:Post')->findAll();
		$response = $this->render('IMDCTerpTubeBundle:Post:index.html.twig',
				array('posts' => $posts)
		);
		return $response;
	}
	
	/**
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @deprecated
	 */
	public function createNewPostAction(Request $request) //TODO delete
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		$user = $this->getUser();
		
		$newpost = new Post();
		$form = $this->createForm(new PostFormType(), $newpost);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			
			$em = $this->getDoctrine()->getManager();
			
			$newpost->setAuthor($user);
			$newpost->setCreated(new \DateTime('now'));
			$newpost->setIsDeleted(FALSE);
			$newpost->setParentThread(NULL);
			
			$user->addPost($newpost);
			$user->increasePostCount(1);
			
			// request to persist message object to database
			$em->persist($newpost);
			$em->persist($user);
			
			// persist all objects to database
			$em->flush();
			 
			$this->get('session')->getFlashBag()->add(
					'info',
					'Post created successfully!'
			);
			return $this->redirect($this->generateUrl('imdc_post_show_all'));
		}
		
		// form not valid, show the basic form
		return $this->render('IMDCTerpTubeBundle:Post:new.html.twig', array(
				'form' => $form->createView(),
		));	
	}

    /**
     * @param Request $request
     * @param $threadid
     * @return RedirectResponse|Response
     * @deprecated
     */
    public function createReplyPostAction(Request $request, $threadid) //TODO delete
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
	
	    $user = $this->getUser();
	    $em = $this->getDoctrine()->getManager();
	
	    $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->findOneBy(array('id' => $threadid));
	    $forum = $thread->getParentForum();
	
	    $newpost = new Post();
	    $newpost->setParentThread($thread);
	    // have to add the em as an option to the form builder
	    //$form = $this->createForm(new PostFormType(), $newpost);
	    $form = $this->createForm(new PostFormFromThreadType(), $newpost, 
	            array('em' => $this->getDoctrine()->getManager(),
	                    'user' => $user,
	                    'thread' => $thread,
	    ));
	    
	    $form->handleRequest($request);
	    
	    $validator = $this->get('validator');
	    $formerrors = $validator->validate($newpost);
	
	    if ($form->isValid()) {
	         
	        $newpost->setAuthor($user);
	        $newpost->setCreated(new \DateTime('now'));

	        // set post temporality
	        if ( NULL != $newpost->getStartTime() && NULL != $newpost->getEndTime() ) {
	            $newpost->setIsTemporal(TRUE);
	        }
	        
	        $user->addPost($newpost);
	        $user->increasePostCount(1); // necessary?
	         
	        // request to persist objects to database
	        $em->persist($user);
	        $em->persist($newpost);
	         
	        // persist all objects to database
            $em->flush();
            
            $newpostid = $newpost->getId();
            
            $thread->addPost($newpost);
            $thread->setLastPostID($newpost->getId());
            $thread->setLastPostAt(new \DateTime('now'));
            
            $logger = $this->container->get('logger');
            $logger->info("\n\n\n*************Forum id is " . $forum->getId() . "******************\n\n\n");
            $forum->setLastActivity(new \DateTime('now'));
            
            $em->persist($forum);            
            $em->persist($thread);

            $em->flush();

	        // doctrine will populate the identity fields whenever one is generated
	        // so accessing the id field after flush will contain the id of newly persisted entity
	        // hopefully	
	
	        $this->get('session')->getFlashBag()->add(
	                'info',
	                'Post created successfully'
	        );
	        
	        // creating the ACL
	        $aclProvider = $this->get('security.acl.provider');
	        $objectIdentity = ObjectIdentity::fromDomainObject($newforum);
	        $acl = $aclProvider->createAcl($objectIdentity);
	        
	        // retrieving the security identity of the currently logged-in user
	        $securityContext = $this->get('security.context');
	        $user = $securityContext->getToken()->getUser();
	        $securityIdentity = UserSecurityIdentity::fromAccount($user);
	        
	        // grant owner access
	        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
	        $aclProvider->updateAcl($acl);

	        return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)).'#'.$newpostid);
	    }
	
	    // form is not valid, show the basic form
	    return $this->render('IMDCTerpTubeBundle:Thread:viewthread.html.twig', array(
	            'form' => $form->createView(),
	            'thread' => $thread,
	            'formerrors' => $formerrors,
	    ));
	}
	
	public function deletePostAction($postid, $threadid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		$user = $this->getUser();
		$em   = $this->getDoctrine()->getManager();
		
		$postToDelete = $em->getRepository('IMDCTerpTubeBundle:Post')->find($postid);
		
		// if post is not owned by the currently logged in user, redirect
		if (!$postToDelete->getAuthor()->getId() == $user->getId()) {
			$this->get('session')->getFlashBag()->add(
					'danger',
					'You do not have permission to delete this post'
			);
			return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)));
		}
		
		// post is owned by the user
		$user->removePost($postToDelete);
		$user->decreasePostCount(1);
		$em->persist($user);
		$em->remove($postToDelete);
		$em->flush();
		
		return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)));
		
	}
	
	public function deletePostAjaxAction(Request $request, $pid) //TODO merge with deletePostAction
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		//$request = $this->get('request');
		//$postid = $request->request->get('pid');
		
		
		// if not ajax, throw an error
		if (!$request->isXmlHttpRequest()) {
			throw new BadRequestHttpException('Only Ajax POST calls accepted');
		}
		
		$user = $this->getUser();
		$em   = $this->getDoctrine()->getManager();
		
		$postToDelete = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
		
		// if post is not owned by the currently logged in user, redirect
		if (!$postToDelete->getAuthor()->getId() == $user->getId()) {
			$this->get('session')->getFlashBag()->add(
					'danger',
					'You do not have permission to delete this post'
			);
			//return $this->redirect($this->generateUrl('imdc_thread_view', array('threadid'=>$threadid)));
			// return an ajax fail here
			$return = array('responseCode' => 400, 'feedback' => 'You do not have permission to delete this post');
		}
		else {
			// post is owned by the user
			$user->removePost($postToDelete);
			$user->decreasePostCount(1);
			$em->persist($user);
			$em->remove($postToDelete);
			$em->flush();
			
			$return = array('responseCode' => 200, 'feedback' => 'Post deleted!');
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	}

    public function newAction(Request $request, $threadId, $pid)
    {
        // if not ajax, throw an exception
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }

        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $thread = null;
        $postParent = null;
        if ($threadId) {
            $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadId);
        } elseif ($pid) {
            $postParent = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        }

        if (!$thread && !$postParent) {
            throw new Exception('thread/post not found');
        }

        //TODO permissions

        $isPostReply = !$thread;
        $post = new Post();
        $postForm = $this->createForm(new PostType(), $post, array(
                'canTemporal' => !$isPostReply ? ($thread->getType() == 1) : false
            ));
        $postForm->handleRequest($request);

        if ($postForm->isValid()) {
            $user = $this->getUser();
            $currentDateTime = new \DateTime('now');
            $post->setAuthor($user);
            $post->setCreated($currentDateTime);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));

            if (!$postForm->get('mediatextarea')->isEmpty()) {
                $mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')
                    ->find($postForm->get('mediatextarea')->getData());
                if (!$mediaFile) {
                    throw new Exception('media not found');
                }

                if (!$user->getResourceFiles()->contains($mediaFile)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$post->getAttachedFile()->contains($mediaFile))
                    $post->addAttachedFile($mediaFile);
            }

            if (!$isPostReply) {
                $post->setParentThread($thread);
            } else {
                $post->setParentPost($postParent);
                $post->setParentThread($postParent->getParentThread());
            }

            $em->persist($post);
            $em->flush();

            if ($isPostReply)
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

            $content = array(
                'wasReplied' => true,
                'redirectUrl' => $this->generateUrl('imdc_thread_view', array(
                        'threadid' => $thread->getId()))
            );
        } else {
            $content = array(
                'wasReplied' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.reply.html.twig', array(
                        'form' => $postForm->createView(),
                        'post' => !$isPostReply ? $post : $postParent,
                        'uploadForms' => MyFilesGatewayController::getUploadForms($this)))
            );
        }

        return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
    }

	public function editAction(Request $request, $pid)
	{
        // if not ajax, throw an error
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }

	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();
		$post = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
        if (!$post) {
            throw new Exception('post not found');
        }

        //TODO permissions
        $user = $this->getUser();
		if (!$post->getAuthor() == $user) {
            throw new AccessDeniedException();
        }

        $postForm = $this->createForm(new PostType(), $post, array(
            'canTemporal' => !$post->getParentPost() ? ($post->getParentThread()->getType() == 1) : false
        ));
        $postForm->handleRequest($request);

        if ($postForm->isValid()) {
            $post->setEditedAt(new \DateTime('now'));
            $post->setEditedBy($user);
            $post->setIsTemporal(is_float($post->getStartTime()) && is_float($post->getEndTime()));

            if (!$postForm->get('mediatextarea')->isEmpty()) {
                $mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')
                    ->find($postForm->get('mediatextarea')->getData());
                if (!$mediaFile) {
                    throw new Exception('media not found');
                }

                if (!$user->getResourceFiles()->contains($mediaFile)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                if (!$post->getAttachedFile()->contains($mediaFile))
                    $post->addAttachedFile($mediaFile);
            }

            $forum = $post->getParentThread()->getParentForum();
            $forum->setLastActivity(new \DateTime('now'));

            $em->persist($post);
            $em->persist($forum);
            $em->flush();

            $content = array(
                'wasEdited' => true,
                'startTime' => $post->getStartTime(),
                'endTime' => $post->getEndTime(),
                'isTemporal' => $post->getIsTemporal(),
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.post.html.twig', array(
                        'post' => $post))
            );
        } else {
            $content = array(
                'wasEdited' => false,
                'html' => $this->renderView('IMDCTerpTubeBundle:Post:ajax.edit.html.twig', array(
                        'form' => $postForm->createView(),
                        'post' => $post,
                        'uploadForms' => MyFilesGatewayController::getUploadForms($this)))
            );
        }

        return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
	}
}
