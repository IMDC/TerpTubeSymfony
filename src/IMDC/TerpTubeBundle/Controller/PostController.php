<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
use IMDC\TerpTubeBundle\Form\Type\PostFormType;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use IMDC\TerpTubeBundle\Form\Type\PostEditFormType;

class PostController extends Controller
{
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$posts = $em->getRepository('IMDCTerpTubeBundle:Post')->findAll();
		$response = $this->render('IMDCTerpTubeBundle:Post:index.html.twig',
				array('posts' => $posts)
		);
		return $response;
	}
	
	public function createNewPostAction(Request $request) 
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
			//$user->increasePostCount(1);
			
			// request to persist message object to database
			$em->persist($newpost);
			$em->persist($user);
			
			// persist all objects to database
			$em->flush();
			 
			$this->get('session')->getFlashBag()->add(
					'notice',
					'Post created successfully!'
			);
			return $this->redirect($this->generateUrl('imdc_post_show_all'));
		}
		
		// form not valid, show the basic form
		return $this->render('IMDCTerpTubeBundle:Post:new.html.twig', array(
				'form' => $form->createView(),
		));	
	}
	
	public function createReplyPostAction(Request $request, $threadid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
	
	    $user = $this->getUser();
	    $em = $this->getDoctrine()->getManager();
	
	    $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->findOneBy(array('id' => $threadid));
	
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
	         
	        // request to persist objects to database
	        $em->persist($user);
	        $em->persist($newpost);
	         
	        // persist all objects to database
	        try {
	            $em->flush();
	            $newpostid = $newpost->getId();
	            $thread->addPost($newpost);
	            $thread->setLastPostID($newpost->getId());
	            $thread->setLastPostAt(new \DateTime('now'));
	            $em->persist($thread);
	            $em->flush();
	        } catch (\PDOException $e) {
	
	        }
	
	        // doctrine will populate the identity fields whenever one is generated
	        // so accessing the id field after flush will contain the id of newly persisted entity
	        // hopefully
	
	
	        $this->get('session')->getFlashBag()->add(
	                'notice',
	                'Post created successfully!'
	        );
	        return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)).'#'.$newpostid);
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
					'error',
					'You do not have permission to delete this post'
			);
			return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)));
		}
		
		// post is owned by the user
		$em->remove($postToDelete);
		$em->flush();
		
		return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)));
		
	}
	
	public function deletePostAjaxAction(Request $request, $pid)
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
					'error',
					'You do not have permission to delete this post'
			);
			//return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)));
			// return an ajax fail here
			$return = array('responseCode' => 400, 'feedback' => 'You do not have permission to delete this post');
		}
		else {
			// post is owned by the user
			$em->remove($postToDelete);
			$em->flush();
			
			$return = array('responseCode' => 200, 'feedback' => 'Post deleted!');
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	}
	
	public function editPostAction(Request $request, $pid)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
	    
	    $user = $this->getUser();
	    $em = $this->getDoctrine()->getManager();
	    
	    $postToEdit = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
	    $postID = $postToEdit->getId();
	    $threadid = $postToEdit->getParentThread()->getId();
	    
	    // does the user own this post?
	    if (!$user->getPosts()->contains($postToEdit)) {
	        $this->get('session')->getFlashBag()->add(
	                'notice',
	                'You do not have permission to edit this post'
	        );
	        return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
	    }
	    
	    $posteditform = $this->createForm(new PostEditFormType(), $postToEdit, 
                	            array('user' => $user,
	    ));
	    
	    $posteditform->handleRequest($request);
	    
	    if ($posteditform->isValid()) {
	    
	        //$postToEdit->setAuthor($user);
	        //$postToEdit->setCreated(new \DateTime('now'));
	        //$postToEdit->setIsDeleted(FALSE);
	        //$postToEdit->setParentThread($thread);
	        $postToEdit->setEditedAt(new \DateTime('now'));
	        $postToEdit->setEditedBy($user);
	    
	        //$user->addPost($newpost);
	    
	        $mediarepo = $em->getRepository('IMDCTerpTubeBundle:Media');
	        if (!$posteditform->get('mediatextarea')->isEmpty()) {
	        
	            $rawmediaID = $posteditform->get('mediatextarea')->getData();
	            $logger = $this->container->get('logger');
	            $logger->info('*************media id is ' . $rawmediaID);
	            $mediaFile = $mediarepo->findOneBy(array('id' => $rawmediaID));
	        
	            if ($user->getResourceFiles()->contains($mediaFile)) {
	                $logger = $this->get('logger');
	                $logger->info('User owns this media file');
	                $postToEdit->addAttachedFile($mediaFile);
	            }
	        
	        }
	        
	        if ($postToEdit->getStartTime() && $postToEdit->getEndTime()) {
	            $postToEdit->setIsTemporal(TRUE);
	        }
	        else {
	            $postToEdit->setIsTemporal(FALSE);
	        }
	        
	        
	        // request to persist objects to database
	        //$em->persist($user);
	        $em->persist($postToEdit);
	        $em->flush();
	    
	        $this->get('session')->getFlashBag()->add(
	                'notice',
	                'Post edited successfully!'
	        );
	        
	        return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)) . '#' . $postID);
	    }
	    
	    // form not valid, show the basic form
	    return $this
	    		->render('IMDCTerpTubeBundle:Post:editPost.html.twig',
	    			array('form' => $posteditform->createView(),
	            			'post' => $postToEdit,
	    ));
	    
	}
	
	public function editPostAjaxAction(Request $request, $pid) 
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
		
		$postToEdit = $em->getRepository('IMDCTerpTubeBundle:Post')->find($pid);
		
		// if post is not owned by the currently logged in user, redirect
		if (!$postToEdit->getAuthor()->getId() == $user->getId()) {
			$this->get('session')->getFlashBag()->add(
					'error',
					'You do not have permission to edit this post'
			);
			//return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)));
			// return an ajax fail here
			$return = array('responseCode' => 400, 'feedback' => 'You do not have permission to edit this post');
		}
		else {
			// post is owned by the user
		    $posteditform = $this->createForm(new PostEditFormType(), $postToEdit,
		                      array('user' => $user,
	                      ));
		    
		    $formhtml = $this->renderView('IMDCTerpTubeBundle:Post:editPostAjax.html.twig',
		                          array('form' => $posteditform->createView(),
		                              'post' => $postToEdit,
		                  ));
		    
			
			$return = array('responseCode' => 200, 'feedback' => 'Form Sent!', 'form' => $formhtml);
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	     
	}
}