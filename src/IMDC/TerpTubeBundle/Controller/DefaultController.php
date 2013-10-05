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

		return $this->render('IMDCTerpTubeBundle:Default:index.html.twig');
	}

	/**
	 * Creates a new user in the database using the FOSUserBundle usermanager
	 * The user has a username of 'noreply' and using a native SQL query, an ID of 0
	 * 
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function createNoReplyOnceAction()
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$em = $this->getDoctrine()->getManager();
		$userManager = $this->container->get('fos_user.user_manager');

		$user = $userManager->createUser();
		$randnum = rand(0, 10000);
		$user->setUsername('noreply');
		$user->setPlainPassword('1mdCu53R');
		$user->setEnabled(true);
		$adminemail = 'noreply-' . $randnum;
		$user->setEmail($adminemail);
		$user->addRole('ROLE_SUPER_ADMIN');

		try
		{
			$userManager->updatePassword($user);
			$userManager->updateUser($user);
			$em->persist($user);
			$em->flush();
			$this->get('session')->getFlashBag()->add('success', 'Noreply user has been successfully created');
		}
		catch (DBALException $e)
		{
			$this->get('session')->getFlashBag()->add('notice', 'User already exists in database');
		}

		$em = $this->getDoctrine()->getManager();
		try
		{
			$affectedrows = $this->getDoctrine()->getRepository('IMDCTerpTubeBundle:User')
					->modifyNoReplyUser($adminemail);
			if ($affectedrows == 1)
			{
				$this->get('session')->getFlashBag()->add('success', 'Noreply user id modified to 0');
			}
			else
			{
				$this->get('session')->getFlashBag()->add('error', 'Noreply user id was not modified');
			}

		}
		catch (DBALException $e)
		{
			$this->get('session')->getFlashBag()->add('error', 'SQL error');
		}
		return $this->render('IMDCTerpTubeBundle:Default:index.html.twig');
	}
}
