<?php

namespace IMDC\TerpTubeBundle\Controller;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;

use IMDC\TerpTubeBundle\Entity\MetaData;

use IMDC\TerpTubeBundle\Entity\ResourceFile;

use Symfony\Component\Intl\Exception\NotImplementedException;

use IMDC\TerpTubeBundle\Form\Type\OtherMediaFormType;

use IMDC\TerpTubeBundle\Form\Type\VideoMediaFormType;

use IMDC\TerpTubeBundle\Event\UploadEvent;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Form\Type\AudioMediaFormType;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use IMDC\TerpTubeBundle\Filter\FileFilter;

use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Finder\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class AddFileGatewayController extends Controller
{

	/**
	 * A gateway form for uploading/recording or selecting existing files
	 * 
	 * @param String $filter
	 * @param String $path
	 * @throws AccessDeniedException
	 * @throws NotFoundHttpException
	 * @throws BadRequestHttpException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function gatewayAction(Request $request, $path)
	{
		$securityContext = $this->container->get('security.context');
		if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add('notice', 'Please log in first');
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

	}

	public function addAudioAction(Request $request, $url)
	{
		$securityContext = $this->container->get('security.context');
		if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add('notice', 'Please log in first');
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$audioMedia = new Media();

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new AudioMediaFormType(), $audioMedia, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$audioMedia->setOwner($userObject);
				$audioMedia->setType(Media::TYPE_AUDIO);
				// flush object to database
				$em = $this->container->get('doctrine')->getManager();
				$em->persist($audioMedia);
				// Remove old avatar from DB:
				$userObject->addResourceFile($audioMedia);

				$em->flush();

				$this->container->get('session')->getFlashBag()
						->add('media', 'Audio file uploaded successfully successfully!');

				$eventDispatcher = $this->container->get('event_dispatcher');
				$uploadedEvent = new UploadEvent($audioMedia);
				$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

				// 				$uploadedEvent->getResponse();
				if ($url === null)
				{
					$response = new RedirectResponse($this->generateUrl('imdc_files_gateway'));
				}
				else
				{
					$response = new RedirectResponse($url);
				}
				return $response;
			}
		}
		// form not valid, show the basic form
		return $this
				->render('IMDCTerpTubeBundle:AddFileGateway:addFile.html.twig', array('form' => $form->createView(),));
	}

	public function addVideoAction(Request $request, $url)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$videoMedia = new Media();

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new VideoMediaFormType(), $videoMedia, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$videoMedia->setOwner($userObject);
				$videoMedia->setType(Media::TYPE_VIDEO);
				// flush object to database
				$em = $this->container->get('doctrine')->getManager();
				$em->persist($videoMedia);
				// Remove old avatar from DB:
				$userObject->addResourceFile($videoMedia);

				$em->flush();

				$this->container->get('session')->getFlashBag()
						->add('media', 'Video file uploaded successfully successfully!');

				$eventDispatcher = $this->container->get('event_dispatcher');
				$uploadedEvent = new UploadEvent($videoMedia);
				$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

				// 				$uploadedEvent->getResponse();
				if ($url === null)
				{
					$response = new RedirectResponse($this->generateUrl('imdc_files_gateway'));
				}
				else
				{
					$response = new RedirectResponse($url);
				}
				return $response;
			}
		}
		// form not valid, show the basic form
		return $this
				->render('IMDCTerpTubeBundle:AddFileGateway:addFile.html.twig', array('form' => $form->createView(),));
	}

	public function addImageAction(Request $request, $url)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$imageMedia = new Media();

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new ImageMediaFormType(), $imageMedia, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$imageMedia->setOwner($userObject);
				$imageMedia->setType(Media::TYPE_IMAGE);
				// flush object to database
				$em = $this->container->get('doctrine')->getManager();
				$em->persist($imageMedia);
				// Remove old avatar from DB:
				$userObject->addResourceFile($imageMedia);

				$em->flush();

				$this->container->get('session')->getFlashBag()
						->add('media', 'Image file uploaded successfully successfully!');

				$eventDispatcher = $this->container->get('event_dispatcher');
				$uploadedEvent = new UploadEvent($imageMedia);
				$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

				// 				$uploadedEvent->getResponse();
				if ($url === null)
				{
					$response = new RedirectResponse($this->generateUrl('imdc_files_gateway'));
				}
				else
				{
					$response = new RedirectResponse($url);
				}
				return $response;
			}
		}
		// form not valid, show the basic form
		return $this
				->render('IMDCTerpTubeBundle:AddFileGateway:addFile.html.twig', array('form' => $form->createView(),));
	}

	public function addRecordingAction(Request $request, $url)
	{
		//FIXME add the recording stuff here
		// 		throw new NotImplementedException("Not yet implemented");

		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$media = new Media();
		$fileName = $media->getId();
		$media->setOwner($userObject);
		$media->setType(Media::TYPE_VIDEO);
		$currentTime = new \DateTime('now');
		$media->setTitle("Recording-". $currentTime->format('Y-m-d-H:i'));
		
		$audioFile = $request->files->get("audio-blob", null);
		$videoFile = $request->files->get("video-blob", null);
		
		//FIXME Need to sync the audio/videos
		$transcoder = $this->container->get('imdc_terptube.transcoder');//($this->get('logger'));
		$mergedFile = $transcoder->mergeAudioVideo($audioFile, $videoFile);
		$resourceFile = new ResourceFile();
		$resourceFile->setMedia($media);
		$resourceFile->setWebmExtension("webm");
		$media->setIsReady(Media::READY_WEBM);
		$resourceFile->setFile($mergedFile);
		
		$media->setResource($resourceFile);
		
		
		$userObject->addResourceFile($media);
		
		$em = $this->container->get('doctrine')->getManager();
		
		$em->persist($resourceFile);
		$em->persist($media);
		
		$em->flush();
		
		$eventDispatcher = $this->container->get('event_dispatcher');
		$uploadedEvent = new UploadEvent($media);
		$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

		$return = array('responseCode' => 200, 'feedback' => 'media added', 'mediaID' => $media->getId());
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json'));
		
// 		return $this->render('IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig');
	}

	public function addOtherAction(Request $request, $url)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		$otherMedia = new Media();

		$formFactory = $this->container->get('form.factory');

		$form = $formFactory->create(new OtherMediaFormType(), $otherMedia, array());

		if ('POST' === $request->getMethod())
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$otherMedia->setOwner($userObject);
				$otherMedia->setType(Media::TYPE_OTHER);
				// flush object to database
				$em = $this->container->get('doctrine')->getManager();
				$em->persist($otherMedia);
				// Remove old avatar from DB:
				$userObject->addResourceFile($otherMedia);

				$em->flush();

				$this->container->get('session')->getFlashBag()
						->add('media', 'File uploaded successfully successfully!');

				$eventDispatcher = $this->container->get('event_dispatcher');
				$uploadedEvent = new UploadEvent($otherMedia);
				$eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

				// 				$uploadedEvent->getResponse();
				if ($url === null)
				{
					$response = new RedirectResponse($this->generateUrl('imdc_files_gateway'));
				}
				else
				{
					$response = new RedirectResponse($url);
				}
				return $response;
			}
		}
		// form not valid, show the basic form
		return $this
				->render('IMDCTerpTubeBundle:AddFileGateway:addFile.html.twig', array('form' => $form->createView(),));
	}

}
