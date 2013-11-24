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

use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Form\Type\ForumFormType;
use IMDC\TerpTubeBundle\Entity\Media;

class ForumController extends Controller
{
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
		$forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->findAll();
		$response = $this->render('IMDCTerpTubeBundle:Forum:index.html.twig',
				array('forums' => $forums)
		);
		return $response;
	}
	
	
	public function newAction(Request $request) 
	{
	    
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
        
        $user = $this->getUser();
        
        $newforum = new Forum();
        $form = $this->createForm(new ForumFormType(), $newforum, array(
                'user' => $this->getUser(),
        ));
        $em = $this->getDoctrine()->getManager();
        
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
                $mediaFile = new Media();
                $mediaFile = $threadrepo->findOneBy(array('id' => $rawmediaID));
                
                // check to make sure the user owns this media file
                if ($user->getResourceFiles()->contains($mediaFile)) {
                    $logger = $this->get('logger');
                    $logger->info('User owns this media file');
                    $newforum->addTitleMedia($mediaFile);
                }
                
            }
           
            $newforum->setCreator($user);
            $newforum->setCreationDate(new \DateTime('now'));
//             $newforum->setLocked(FALSE);
//             $newforum->setSticky(FALSE);
            $newforum->setLastActivity(new \DateTime('now'));
            	
            $user->addForum($newforum);
//             $user->increasePostCount(1);
            	
            // request to persist message object to database
            $em->persist($newforum);
            $em->persist($user);
            	
            // persist all objects to database
            $em->flush();
        
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Forum created successfully!'
            );
            
            return $this->redirect($this->generateUrl('imdc_forum_list'));
        }
        
        // form not valid, show the basic form
        return $this->render('IMDCTerpTubeBundle:Forum:new.html.twig',
                array('form' => $form->createView(),
        ));
	    
	}
	
	public function viewAction(Request $request, $forumid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	    
	    $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->findOneBy(array('id' => $forumid));
	    $response = $this->render('IMDCTerpTubeBundle:Forum:view.html.twig',
	        array('forum' => $forum)
	    );
	    return $response;
	}
	
}