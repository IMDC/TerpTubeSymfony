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


class PostController extends Controller
{
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$posts = $em->getRepository('IMDCTerpTubeBundle:Post')
		->findAll();
		$response = $this->render('IMDCTerpTubeBundle:Post:index.html.twig',
				array('posts' => $posts)
		);
		return $response;
	}
	
	public function createNewPostAction(Request $request) 
	{
		// check if user logged in
		$securityContext = $this->container->get('security.context');
		if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add(
					'notice',
					'Please log in first'
			);
			return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
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
	    $securityContext = $this->container->get('security.context');
	    if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
	    {
	        $this->get('session')->getFlashBag()->add(
	                'notice',
	                'Please log in first'
	        );
	        return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
	    }
	
	    $user = $this->getUser();
	    $em = $this->getDoctrine()->getManager();
	
	    $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->findOneBy(array('id' => $threadid));
	
	    $newpost = new Post();
	    $newpost->setParentThread($thread);
	    // have to add the em as an option to the form builder
	    //$form = $this->createForm(new PostFormType(), $newpost);
	    $form = $this->createForm(new PostFormType(), $newpost, array('em' => $this->getDoctrine()->getManager()));
	
	    $form->handleRequest($request);
	
	    if ($form->isValid()) {
	         
	        $newpost->setAuthor($user);
	        $newpost->setCreated(new \DateTime('now'));
	        $newpost->setIsDeleted(FALSE);
	        //$newpost->setParentThread($thread);
	         
	        $user->addPost($newpost);
	         
	        // request to persist objects to database
	        $em->persist($user);
	        $em->persist($newpost);
	         
	        // persist all objects to database
	        try {
	            $em->flush();
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
	        return $this->redirect($this->generateUrl('imdc_thread_view_specific', array('threadid'=>$threadid)));
	    }
	
	    // form not valid, show the basic form
	    return $this->render('IMDCTerpTubeBundle:Post:new.html.twig', array(
	            'form' => $form->createView(),
	    ));
	}
	
	public function deletePostAction($postid, $threadid)
	{
		// check if user logged in
		$securityContext = $this->container->get('security.context');
		if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add(
					'notice',
					'Please log in first'
			);
			return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
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
		$securityContext = $this->container->get('security.context');
		if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add(
					'notice',
					'Please log in first'
			);
			return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
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
		
		$logger = $this->get('logger');
		$logger->info('******OMG IM HERE******');
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
}