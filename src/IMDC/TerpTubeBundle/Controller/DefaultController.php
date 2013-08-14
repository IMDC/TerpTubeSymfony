<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IMDC\TerpTubeBundle\Entity\UserGroupRepository;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\DBAL\DBALException;


class DefaultController extends Controller
{
    public function indexAction()
    {
        //return $this->render('IMDCTerpTubeBundle:Default:index.html.twig', array('name' => $name));
        // return $this->render('<html><body>Hello world</body></html>');
        return $this->render('IMDCTerpTubeBundle:Default:index.html.twig');
        // return array();
    }
    
    public function createNoReplyOnceAction() 
    {
        // check if user logged in
        $securityContext = $this->container->get('security.context');
        if(! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in first'
            );
            return $this->redirect($this->generateUrl('imdc_terp_tube_homepage'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        
        $user = $userManager->createUser();
        $randnum = rand(0, 10000);
        $user->setUsername('noreply');
        $user->setPlainPassword('1mdCu53R');
        $user->setEnabled(true);
        $adminemail = 'noreply-' . $randnum;
        $user->setEmail($adminemail);
        $user->addRole('ROLE_SUPER_ADMIN');
        
        try {
            $userManager->updatePassword($user);
            $userManager->updateUser($user);
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                    'success',
                    'Noreply user has been successfully created');
        } catch (DBALException $e) {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'User already exists in database');
        }
        
        $em = $this->getDoctrine()->getManager();
        try {
            $affectedrows = $this->getDoctrine()
                        ->getRepository('IMDCTerpTubeBundle:User')
                        ->modifyNoReplyUser($adminemail);
            if ($affectedrows == 1) {
                $this->get('session')->getFlashBag()->add(
                        'success',
                        'Noreply user id modified to 0');
            }
            else {
                $this->get('session')->getFlashBag()->add(
                        'error',
                        'Noreply user id was not modified');
            }

        } catch (DBALException $e) {
            $this->get('session')->getFlashBag()->add(
                    'error',
                    'SQL error');
        }

        return $this->render('IMDCTerpTubeBundle:Default:index.html.twig');

    }
        
        
}
