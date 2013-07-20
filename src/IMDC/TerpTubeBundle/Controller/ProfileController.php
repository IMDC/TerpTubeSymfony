<?php

namespace IMDC\TerpTubeBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;

use FOS\UserBundle\Controller\ProfileController as BaseController;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProfileController extends BaseController
{
	// 	public function showAction()
	// 	{
	// 		$securityContext = $this->container->get('security.context');
	// 		if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
	// 		{
	// 			return $this->render('IMDCTerpTubeBundle:Home:index.html.twig');
	// 			//			return new Response('<html><body>Hello!</body></html>');
	// 		}
	// 		else
	// 		{
	// 			$response = new RedirectResponse($this->generateUrl('imdc_terp_tube_homepage'));
	// 			return $response;
	// 			//return new Response('<html><body>Bye!</body></html>');
	// 		}

	// 		// return $this->render('<html><body>Hello world</body></html>');
	// 		//return new Response('<html><body>Hello!</body></html>');
	// 		// return array();
	// 	}

	public function showAction()
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$response = new RedirectResponse($this->container->get('router')->generate('imdc_terp_tube_user_profile_specific', array('userName'=>$user->getUsername())));
		return $response;
// 		$userManager = $this->container->get('fos_user.user_manager');
// 		$userObject = $userManager->findUserByUsername($user);
// 		$profile = $userObject->getProfile();
// 	//	var_dump($user);
// 	//	var_dump($profile);
// 		return $this->container->get('templating')
// 				->renderResponse(
// 						'IMDCTerpTubeBundle:Profile:show.html.'
// 								. $this->container->getParameter('fos_user.template.engine'), array('user' => $userObject, 'profile' =>$profile));

	}
	public function showSpecificAction($userName)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($userName);
		$profile = $userObject->getProfile();
		//	var_dump($user);
		//	var_dump($profile);
		return $this->container->get('templating')
		->renderResponse(
				'IMDCTerpTubeBundle:Profile:show.html.'
				. $this->container->getParameter('fos_user.template.engine'), array('user' => $userObject, 'profile' =>$profile));
	
	}

	/**
	 * Edit the user
	 */
	public function editAction(Request $request)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		$profile = $userObject->getProfile();

		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->container->get('event_dispatcher');

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

		if (null !== $event->getResponse())
		{
			return $event->getResponse();
		}

		/** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
		$formFactory = $this->container->get('fos_user.profile.form.factory');

		$form = $formFactory->createForm();
		$form->setData($profile);

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
				$userManager = $this->container->get('fos_user.user_manager');

				$event = new FormEvent($form, $request);
				$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

				$userManager->updateUser($user);

				if (null === $response = $event->getResponse())
				{
					$url = $this->container->get('router')->generate('imdc_terp_tube_user_profile');
					$response = new RedirectResponse($url);
				}

				$dispatcher
						->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED,
								new FilterUserResponseEvent($user, $request, $response));

				return $response;
			}
		}

		return $this->container->get('templating')
				->renderResponse(
						'IMDCTerpTubeBundle:Profile:edit.html.' . $this->container->getParameter('fos_user.template.engine'),
						array('form' => $form->createView()));
	}
}
