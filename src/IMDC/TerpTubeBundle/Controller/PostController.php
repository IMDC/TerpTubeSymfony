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
	
	public function createNewPostFromMediaAction($resourceid)
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
	    
	    $this->get('session')->getFlashBag()->add(
					'notice',
					'Not implemented yet'
			);
			return $this->redirect($this->generateUrl('imdc_post_show_all'));
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
}