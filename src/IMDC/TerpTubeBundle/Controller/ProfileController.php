<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Form\Type\LanguageFormType;
use IMDC\TerpTubeBundle\Entity\Language;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use Symfony\Component\Form\FormFactory;
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
use IMDC\TerpTubeBundle\Utils\Utils;


use FOS\UserBundle\Controller\ProfileController as BaseController;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use IMDC\TerpTubeBundle\Form\Type\MediaType;

class ProfileController extends Controller
{

	/**
	 * Show your own profile
	 * @throws AccessDeniedException - occurs if you are not logged in
	 */
	public function showAction() // edit to match fosuserbundle declaration
	{
	    $request = $this->container->get('request'); //edited to match fosuserbundle declaration
		$user = $this->container->get('security.context')->getToken()->getUser();

        return $this->forward('IMDCTerpTubeBundle:Profile:showSpecific', array(
            'userName' => $user->getUsername()
        ));
	}

	/**
	 * Show the profile of a specific user
	 * @param unknown_type $userName - The user's profile to show
	 * @throws AccessDeniedException - occurs if you are not logged in
	 * @throws NotFoundHttpException - occurs if the user does not exist
	 */
	public function showSpecificAction(Request $request, $userName)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($userName);
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}

		return $this->render('IMDCTerpTubeBundle:Profile:view.html.twig', array(
            'user' => $userObject,
            'profile' => $userObject->getProfile()
        ));
	}

	public function updateAvatarAction(Request $request/*, $userName*/)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		/*if ($user->getUsername() != $userName)
		{
			$response = new RedirectResponse(
					$this->container->get('router')
							->generate('imdc_profile_user', array('userName' => $userName)));
			return $response;
		}*/
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		/** @var \IMDC\TerpTubeBundle\Entity\UserProfile $profile  */
		$profile = $userObject->getProfile();

		$avatar = new Media();
		$avatar
				->setTitle(
						$this->container->get('translator')
								->trans('profile.show.avatar', array(), 'IMDCTerpTubeBundle'));

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new MediaType(), $avatar, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$uploadedFile = $avatar->getResource()->getFile();
				$type = Utils::getUploadedFileType($uploadedFile);
				if ($type == Media::TYPE_IMAGE)
				{
					$avatar->setOwner($userObject);
					$avatar->setType(Media::TYPE_IMAGE);
					// flush object to database
					$em = $this->container->get('doctrine')->getManager();
					$em->persist($avatar);
					// Remove old avatar from DB:
					if (($oldAvatar = $profile->getAvatar()) !== null)
						$em->remove($profile->getAvatar());
					$profile->setAvatar($avatar);
					
					$em->flush();
					
					$this->container->get('session')->getFlashBag()->add('info', 'Avatar updated successfully!');
					
					$eventDispatcher = $this->container->get('event_dispatcher');
					$uploadedEvent = new UploadEvent($avatar);
					$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);
					
					$url = $this->container->get('router')->generate('imdc_profile_me');
					$response = new RedirectResponse($url);
					return $response;
				}
				else 
				{
					$this->container->get('session')->getFlashBag()->add('warning', 'You must select an image for an avatar!');
				}
				
			}
		}

        return $this->render('IMDCTerpTubeBundle:Profile:editAvatar.html.twig', array(
            'form' => $form->createView()
        ));
	}

	/**
	 * Edit the user
	 * If you try to edit a different user, not your own, you are redirected to only show their profile
	 */
	public function editAction(Request $request) //edit to match fosuserbundle declaration
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		
		/*
		$userName = $request->query->get('userName');
		if ($user->getUsername() != $userName)
		{
			$response = new RedirectResponse(
					$this->container->get('router')
							->generate('imdc_profile_user', array('userName' => $userName)));
			return $response;
		}
		*/
		
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
					$url = $this->container->get('router')->generate('imdc_profile_me');
					$response = new RedirectResponse($url);
				}

				$dispatcher
						->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED,
								new FilterUserResponseEvent($user, $request, $response));

				return $response;
			}
		}

		return $this->render('IMDCTerpTubeBundle:Profile:edit.html.twig', array(
            'form' => $form->createView(),
			'profile' => $user->getProfile()
        ));
	}
}
