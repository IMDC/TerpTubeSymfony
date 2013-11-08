<?php

namespace IMDC\TerpTubeBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Model\UserManager;
use IMDC\TerpTubeBundle\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserController extends Controller
{
    public function indexAction(Request $request, $pagenumber)
    {
        $em = $this->getDoctrine()->getManager();
        
        /*
        //$members = $em->getRepository('IMDCTerpTubeBundle:User')->findAll();
        $members = $em->getRepository('IMDCTerpTubeBundle:User')->findPublicListedMembers(12);
        
        $response = $this->render('IMDCTerpTubeBundle:Member:index.html.twig',
            array('members' => $members)
        );
        return $response;
        */
        
        $firstresult = 0;
        $maxresults = 12;
        $dql = "SELECT p
                FROM IMDCTerpTubeBundle:UserProfile p";
        
        $query = $em->createQuery($dql)
                    ->setFirstResult($firstresult)
                    ->setMaxResults($maxresults);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        /*
        $c = count($paginator);
        foreach ($paginator as $user) {

        }
        */
        $response = $this->render('IMDCTerpTubeBundle:Member:index.html.twig',
            array('members' => $paginator, $count = count($paginator)));
        
        
        /*
        $count = $em->createQuery('SELECT count(u) from IMDCTerpTubeBundle:User u')
                    ->getSingleScalarResult();
        
        $dql = "SELECT u
                FROM IMDCTerpTubeBundle:User u
                JOIN IMDCTerpTubeBundle:UserProfile p
                WHERE p.profileVisibleToPublic = true
                AND u.username <> 'noreply'
                ORDER BY u.joinDate DESC";
        $query = $em->createQuery($dql)
                    ->setHint('knp_paginator.count', $count);
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /* page number *//*,
            12 /*limit per page*/
        /*);
        
        return $this->render('IMDCTerpTubeBundle:Members:index.html.twig', array('pagination' => $pagination));
        */
                
    }

}