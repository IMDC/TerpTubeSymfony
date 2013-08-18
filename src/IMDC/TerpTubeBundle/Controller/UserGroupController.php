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
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\UserGroupType;


class UserGroupController extends Controller
{
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        
        $usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')
                          ->findAll();
        $response = $this->render('IMDCTerpTubeBundle:UserGroup:index.html.twig',
                array('usergroups' => $usergroups)
        );
        return $response;
    }
    
    public function viewGroupAction($groupname)
    {
        $em = $this->getDoctrine()->getManager();
        
        $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('name' => $groupname));
        
        $response = $this->render('IMDCTerpTubeBundle:UserGroup:viewgroup.html.twig', 
                                array('usergroup' => $usergroup));
        return $response;
    }
    
    public function createNewUserGroupAction(Request $request)
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
        
        $newusergroup = new UserGroup();
        
        $form = $this->createForm(new UserGroupType(), $newusergroup);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
        
            $newusergroup->setUserFounder($user);
            $newusergroup->addAdmin($user);
            $newusergroup->addMember($user);
            
            $em = $this->getDoctrine()->getManager();
            
            // request to persist message object to database
            $em->persist($newusergroup);
            
            // persist all objects to database
            $em->flush();
             
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'UserGroup created successfully!'
            );
            return $this->redirect($this->generateUrl('imdc_groups_show_all'));
            
            }
            
            // form not valid, show the basic form
            return $this->render('IMDCTerpTubeBundle:UserGroup:new.html.twig', array(
                    'form' => $form->createView(),
            ));
        
       
        
        
        
        
        
    }
}