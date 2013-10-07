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
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Form\Type\ThreadFormType;
use IMDC\TerpTubeBundle\Form\Type\ThreadFromMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\PostFormType;
use IMDC\TerpTubeBundle\Form\Type\PostFormFromThreadType;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\Media;


class ThreadController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
    
        $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getMostRecentThreads(30);
        $response = $this->render('IMDCTerpTubeBundle:Thread:index.html.twig',
                array('threads' => $threads)
        );
        return $response;

    }
    
    public function viewThreadAction(Request $request, $threadid) 
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadid);
        $threadposts = $thread->getPosts();
        
        $newpost = new Post();
        $postform = $this->createForm(new PostFormFromThreadType(), $newpost, array(
        		'user' => $user,
        		'em' => $em,
        ));
        $em = $this->getDoctrine()->getManager();
        
        $postform->handleRequest($request);
        
        if ($postform->isValid()) {
        	 
        	//$em = $this->getDoctrine()->getManager();
        	 
        	
        	$mediarepo = $em->getRepository('IMDCTerpTubeBundle:Media');
        	if (!$postform->get('mediatextarea')->isEmpty()) {
        
        		$rawmediaID = $postform->get('mediatextarea')->getData();
        		$logger = $this->container->get('logger');
        		$logger->info('*************media id is ' . $rawmediaID);
        		$mediaFile = $mediarepo->findOneBy(array('id' => $rawmediaID));
        
        		if ($user->getResourceFiles()->contains($mediaFile)) {
        			$logger = $this->get('logger');
        			$logger->info('User owns this media file');
        			$newpost->addAttachedFile($mediaFile);
        		}
        
        	}
        	 
        	$newpost->setAuthor($user);
        	$newpost->setCreated(new \DateTime('now'));
        	$newpost->setIsDeleted(FALSE);
        	$newpost->setParentThread($thread);
        	 
        	$thread->setLastPostAt(new \DateTime('now'));
        	$user->addPost($newpost);
        	 
        	// request to persist user object to database
        	 
        	// persist all objects to database
        	$em->persist($user);
        	$em->persist($thread);
        	$em->persist($newpost);
        	$em->flush();
        	
        	$thread->setLastPostID($newpost->getId());
        	$em->persist($thread);
        	$em->flush();
        	
        	$this->get('session')->getFlashBag()->add(
        			'notice',
        			'Post created successfully!'
        	);
        	
        	// retrieve all posts for this thread
        	$thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadid);
        	$threadposts = $thread->getPosts();
        	
        	// create a new form
        	$post = new Post();
        	$form = $this->createForm(new PostFormFromThreadType(), $post, array(
        			'user' => $user,
        			'em' => $em,
        	));
        	
        	return $this->render('IMDCTerpTubeBundle:Thread:viewthread.html.twig', array(
        			'form' => $form->createView(),
        			'thread' => $thread,
        			'threadposts' => $threadposts,
        	        'threadsjson' => json_encode($threadposts),
        	));
        }
        
        // form not valid, show the thread
        return $this->render('IMDCTerpTubeBundle:Thread:viewthread.html.twig', array(
                'form' => $postform->createView(),
        		'thread' => $thread,
                'threadposts' => $threadposts,
        ));
    }
    
    
    public function createNewThreadAction(Request $request)
    {
    	// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
        
        $user = $this->getUser();
        
        $newthread = new Thread();
        $form = $this->createForm(new ThreadFormType(), $newthread, array(
                'user' => $this->getUser(),
        ));
        $em = $this->getDoctrine()->getManager();
        /*
        $form = $this->createForm(new ThreadFormType(), $newthread, array(
                'em' => $em,
        ));
        */
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            	
            //$em = $this->getDoctrine()->getManager();
        	
            $threadrepo = $em->getRepository('IMDCTerpTubeBundle:Media');
            // if the media text area isn't empty, the user has selected a media
            // file to create a new thread with
            if (!$form->get('mediatextarea')->isEmpty()) {
                
                $rawmediaID = $form->get('mediatextarea')->getData();
                $logger = $this->container->get('logger');
                $logger->info('*************media id is ' . $rawmediaID);
                /** @var $mediaFile IMDC\TerpTubeBundle\Entity\Media */
                $mediaFile = $threadrepo->findOneBy(array('id' => $rawmediaID));
                
                // check to make sure the user owns this media file
                if ($user->getResourceFiles()->contains($mediaFile)) {
                    $logger = $this->get('logger');
                    $logger->info('User owns this media file');
                    $newthread->addMediaIncluded($mediaFile);
                    $newthread->setType($mediaFile->getType());
                }
                
            }
            
            
            
            /*
            // split up the media id by whitespace
            $rawmediaID = trim($form->get('mediaID')->getData());
            try {
                $mediaFile = $threadrepo->findOneBy(array('id' => $rawmediaID));
                $newthread->addMediaIncluded($mediaFile);
            } catch (\PDOException $e) {
                // todo: create message to user about media file not found
                $logger = $this->get('logger');
                $logger->err('Couldnt find media id ' . $rawmediaID);
            }
            */
            /*
            $rawmediaids = explode(' ', $form->get('mediaID')->getData());
            if ($rawmediaids) {
                foreach ($rawmediaids as $possmedia) {
                    try {
                        $mediaFile = $threadrepo->findOneBy(array('id' => $possmedia));
                        $newthread->addMediaIncluded($mediaFile);
                    } catch (\PDOException $e) {
                        // todo: create message to user about media file not found
                    }
                }
            }
            */
           
            $newthread->setCreator($user);
            $newthread->setCreationDate(new \DateTime('now'));
            $newthread->setLocked(FALSE);
            $newthread->setSticky(FALSE);
            $newthread->setLastPostAt(new \DateTime('now'));
            	
            $user->addThread($newthread);
            	
            // request to persist message object to database
            $em->persist($newthread);
            $em->persist($user);
            	
            // persist all objects to database
            $em->flush();
        
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Thread created successfully!'
            );
            return $this->redirect($this->generateUrl('imdc_thread_show_recent'));
        }
        
        // form not valid, show the basic form
        return $this->render('IMDCTerpTubeBundle:Thread:new.html.twig',
                array('form' => $form->createView(),
        ));
    }
    
    public function createNewThreadFromMediaAction(Request $request, $resourceid)
    {
        
    	// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
         
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $newthread = new Thread();
         
        $chosenmedia = $em->getRepository('IMDCTerpTubeBundle:Media')->find($resourceid);
        
        //$newthread->setMediaIncluded($chosenmedia);
         
        $form = $this->createForm(new ThreadFromMediaFormType(), $newthread, array(
                'user' => $this->getUser(),
                'resource' => $chosenmedia,
        ));
         
        $form->handleRequest($request);
         
        if ($form->isValid()) {
        
            $newthread->setCreator($user);
            $newthread->setCreationDate(new \DateTime('now'));
            $newthread->setLocked(FALSE);
            $newthread->setSticky(FALSE);
            $newthread->setLastPostAt(new \DateTime('now'));
        
            $user->addThread($newthread);
        
            // request to persist message object to database
            $em->persist($newthread);
            $em->persist($user);
        
            // persist all objects to database
            $em->flush();
             
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Thread created successfully!'
            );
            return $this->redirect($this->generateUrl('imdc_thread_show_recent'));
        }
         
        // form not valid, show the basic form
        return $this
                ->render('IMDCTerpTubeBundle:Thread:newfrommedia.html.twig',
                	array('form' => $form->createView(),
                        	'mediaFile' => $chosenmedia,));
    }
    
}