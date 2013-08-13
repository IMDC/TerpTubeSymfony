<?php

namespace IMDC\TerpTubeBundle\Controller;
use IMDC\TerpTubeBundle\Form\Type\LanguageFormType;

use IMDC\TerpTubeBundle\Entity\Language;

use IMDC\TerpTubeBundle\Event\UploadEvent;

use Symfony\Component\Form\FormFactory;

use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Entity\ResourceFile;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

	/**
	 * Show your own profile
	 * @throws AccessDeniedException - occurs if you are not logged in
	 */
	public function showAction()
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$response = new RedirectResponse(
				$this->container->get('router')
						->generate('imdc_terp_tube_user_profile_specific', array('userName' => $user->getUsername())));
		return $response;

	}

	/**
	 * Show the profile of a specific user
	 * @param unknown_type $userName - The user's profile to show
	 * @throws AccessDeniedException - occurs if you are not logged in
	 * @throws NotFoundHttpException - occurs if the user does not exist
	 */
	public function showSpecificAction($userName)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($userName);
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$profile = $userObject->getProfile();
		return $this->container->get('templating')
				->renderResponse(
						'IMDCTerpTubeBundle:Profile:show.html.'
								. $this->container->getParameter('fos_user.template.engine'),
						array('user' => $userObject, 'profile' => $profile));

	}
	
	public function updateAvatarAction(Request $request, $userName)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		if ($user->getUsername() != $userName)
		{
			$response = new RedirectResponse(
					$this->container->get('router')
							->generate('imdc_terp_tube_user_profile_specific', array('userName' => $userName)));
			return $response;
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		/** @var \IMDC\TerpTubeBundle\Entity\UserProfile $profile  */
		$profile = $userObject->getProfile();

		$avatar = new Media();

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new ImageMediaFormType(), $avatar, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$avatar->setOwner($userObject);
				$avatar->setType(Media::TYPE_IMAGE);
				// flush object to database
				$em = $this->container->get('doctrine')->getManager();
				$em->persist($avatar);
				// Remove old avatar from DB:
				if (($oldAvatar = $profile->getAvatar())!== null )
					$em->remove($profile->getAvatar());
				$profile->setAvatar($avatar);

				$em->flush();

				$this->container->get('session')->getFlashBag()->add('avatar', 'Avatar updated successfully!');

				$eventDispatcher = $this->container->get('event_dispatcher');
				$uploadedEvent = new UploadEvent($avatar);
				$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);
				
				$url = $this->container->get('router')->generate('imdc_terp_tube_user_profile');
				$response = new RedirectResponse($url);
				return $response;
			}
		}
		// form not valid, show the basic form
		return $this->container->get('templating')
				->renderResponse(
						'IMDCTerpTubeBundle:Profile:edit_avatar.html.'
								. $this->container->getParameter('fos_user.template.engine'),
						array('form' => $form->createView()));
	}

	/**
	 * Edit the user
	 * If you try to edit a different user, not your own, you are redirected to only show their profile
	 */
	public function editAction(Request $request, $userName)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		if ($user->getUsername() != $userName)
		{
			$response = new RedirectResponse(
					$this->container->get('router')
							->generate('imdc_terp_tube_user_profile_specific', array('userName' => $userName)));
			return $response;
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		/** @var $profile \IMDC\TerpTubeBundle\Entity\UserProfile */
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
						'IMDCTerpTubeBundle:Profile:edit.html.'
								. $this->container->getParameter('fos_user.template.engine'),
						array('form' => $form->createView()));
	}
}
