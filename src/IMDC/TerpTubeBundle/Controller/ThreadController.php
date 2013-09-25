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
use IMDC\TerpTubeBundle\Form\Type\PostFormType;
use IMDC\TerpTubeBundle\Entity\ResourceFile;


class ThreadController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
    
        $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')
                        ->getMostRecentThreads(30);
        $response = $this->render('IMDCTerpTubeBundle:Thread:index.html.twig',
                array('threads' => $threads)
        );
        return $response;

    }
    
    public function viewThreadAction($threadid) 
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
        
        $thread = $em->getRepository('IMDC\TerpTubeBundle\Entity\Thread')->find($threadid);
        $threadposts = $thread->getPosts();
        
        return $this->render('IMDCTerpTubeBundle:Thread:viewthread.html.twig', array(
                'thread' => $thread,
                'threadposts' => $threadposts,
        ));
    }
    
    
    public function createNewThreadAction(Request $request)
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
        
        $newthread = new Thread();
        $form = $this->createForm(new ThreadFormType(), $newthread);
        
        /*
        $form = $this->createForm(new ThreadFormType(), $newthread, array(
                'em' => $this->getDoctrine()->getManager(),
        ));
        */
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            	
            $em = $this->getDoctrine()->getManager();
            	
            $threadrepo = $em->getRepository('IMDCTerpTubeBundle:Media');
            // split up the media id by whitespace
            $rawmediaids = split(' ', $form->get('mediaID')->getData());
            foreach ($rawmediaids as $possmedia) {
                try {
                    $mediaFile = $threadrepo->findOneBy(array('id' => $possmedia));
                    $newthread->addMediaIncluded($mediaFile);
                } catch (\PDOException $e) {
                    // todo: create message to user about media file not found
                }
            }
            
            
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
        return $this->render('IMDCTerpTubeBundle:Thread:new.html.twig', array(
                'form' => $form->createView(),
        ));
    }
    
}