<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity\CompoundMedia;
use IMDC\TerpTubeBundle\Model\JSEntities;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class AddFileGatewayController extends Controller {
	
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
	public function gatewayAction(Request $request) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$user = $this->getUser ();
		$resourceFiles = $user->getResourceFiles ();
		
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'index.html.twig', array (
				'resourceFiles' => $resourceFiles 
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addAudioAction(Request $request, $url) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		
		$user = $this->getUser ();
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$audioMedia = new Media ();
		
		$formFactory = $this->container->get ( 'form.factory' );
		
		$form = $formFactory->create ( new AudioMediaFormType (), $audioMedia, array () );
		
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		
		if ('POST' === $request->getMethod ()) {
			$form->bind ( $request );
			
			if ($form->isValid ()) {
				$audioMedia->setOwner ( $userObject );
				$audioMedia->setType ( Media::TYPE_AUDIO );
				// flush object to database
				$em = $this->container->get ( 'doctrine' )->getManager ();
				$em->persist ( $audioMedia );
				// Remove old avatar from DB:
				$userObject->addResourceFile ( $audioMedia );
				
				$em->flush ();
				
				$this->container->get ( 'session' )->getFlashBag ()->add ( 'media', 'Audio file uploaded successfully!' );
				
				$eventDispatcher = $this->container->get ( 'event_dispatcher' );
				$uploadedEvent = new UploadEvent ( $audioMedia );
				$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				
				// $uploadedEvent->getResponse();
				if ($request->isXmlHttpRequest ()) {
					$response = array (
							'page' => null,
							'finished' => true,
							'media' => JSEntities::getMediaObject ( $audioMedia )
					);
					$response = json_encode ( $response ); // json encode the array
					return new Response ( $response, 200, array (
							'Content-Type' => 'application/json' 
					) );
				} else if ($url === null) {
					$response = new RedirectResponse ( $this->generateUrl ( 'imdc_files_gateway' ) );
				} else {
					$response = new RedirectResponse ( $url );
				}
				return $response;
			}
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
				'form' => $form->createView (),
				'postUrl' => $this->generateUrl ( 'imdc_files_gateway_audio' )
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addVideoAction(Request $request, $url) {
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$videoMedia = new Media ();
		
		$formFactory = $this->container->get ( 'form.factory' );
		
		$form = $formFactory->create ( new VideoMediaFormType (), $videoMedia, array () );
		
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		
		if ('POST' === $request->getMethod ()) {
			$form->bind ( $request );
			
			if ($form->isValid ()) {
				$videoMedia->setOwner ( $userObject );
				$videoMedia->setType ( Media::TYPE_VIDEO );
				// flush object to database
				$em = $this->container->get ( 'doctrine' )->getManager ();
				$em->persist ( $videoMedia );
				// Remove old avatar from DB:
				$userObject->addResourceFile ( $videoMedia );
				
				$em->flush ();
				
				$this->container->get ( 'session' )->getFlashBag ()->add ( 'media', 'Video file uploaded successfully!' );
				
				$eventDispatcher = $this->container->get ( 'event_dispatcher' );
				$uploadedEvent = new UploadEvent ( $videoMedia );
				$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				
				// $uploadedEvent->getResponse();
				if ($request->isXmlHttpRequest ()) {
					$response = array (
							'page' => null,
							'finished' => true,
							'media' => JSEntities::getMediaObject ( $videoMedia )
					);
					$response = json_encode ( $response ); // json encode the array
					return new Response ( $response, 200, array (
							'Content-Type' => 'application/json' 
					) );
				} else if ($url === null) {
					$response = new RedirectResponse ( $this->generateUrl ( 'imdc_files_gateway' ) );
				} else {
					$response = new RedirectResponse ( $url );
				}
				return $response;
			}
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
				'form' => $form->createView (),
				'postUrl' => $this->generateUrl ( 'imdc_files_gateway_video' )
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addImageAction(Request $request, $url) {
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$imageMedia = new Media ();
		
		$formFactory = $this->container->get ( 'form.factory' );
		
		$form = $formFactory->create ( new ImageMediaFormType (), $imageMedia, array () );
		
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		
		if ('POST' === $request->getMethod ()) {
			$form->bind ( $request );
			
			if ($form->isValid ()) {
				$imageMedia->setOwner ( $userObject );
				$imageMedia->setType ( Media::TYPE_IMAGE );
				// flush object to database
				$em = $this->container->get ( 'doctrine' )->getManager ();
				$em->persist ( $imageMedia );
				// Remove old avatar from DB:
				$userObject->addResourceFile ( $imageMedia );
				
				$em->flush ();
				
				$this->container->get ( 'session' )->getFlashBag ()->add ( 'media', 'Image file uploaded successfully!' );
				
				$eventDispatcher = $this->container->get ( 'event_dispatcher' );
				$uploadedEvent = new UploadEvent ( $imageMedia );
				$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				
				// $uploadedEvent->getResponse();
				if ($request->isXmlHttpRequest ()) {
					$response = array (
							'page' => null,
							'finished' => true,
							'media' => JSEntities::getMediaObject ( $imageMedia )
					);
					$response = json_encode ( $response ); // json encode the array
					return new Response ( $response, 200, array (
							'Content-Type' => 'application/json' 
					) );
				} else if ($url === null) {
					$response = new RedirectResponse ( $this->generateUrl ( 'imdc_files_gateway' ) );
				} else {
					$response = new RedirectResponse ( $url );
				}
				return $response;
			}
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
				'form' => $form->createView (),
				'postUrl' => $this->generateUrl ( 'imdc_files_gateway_image' )
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addVideoRecordingAction(Request $request, $url) {
		// FIXME add the recording stuff here
		// throw new NotImplementedException("Not yet implemented");
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$recorderConfiguration = $request->get ( "recorderConfiguration" );
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'recordVideo.html.twig', array (
				"recorderConfiguration" => $recorderConfiguration 
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addAudioRecordingAction(Request $request, $url) {
		// FIXME add the recording stuff here
		throw new NotImplementedException ( "Not yet implemented" );
		
		$user = $this->container->get ( 'security.context' )->getToken ()->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager:' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'recordAudio.html.twig' );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
	public function addRecordingAction(Request $request, $url) {
		// FIXME add the recording stuff here
		// throw new NotImplementedException("Not yet implemented");
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$media = new Media ();
		$fileName = $media->getId ();
		$media->setOwner ( $userObject );
		$media->setType ( Media::TYPE_VIDEO );
		$currentTime = new \DateTime ( 'now' );
		$media->setTitle ( "Recording-" . $currentTime->format ( 'Y-m-d-H:i' ) );
		
		$audioFile = $request->files->get ( "audio-blob", null );
		$videoFile = $request->files->get ( "video-blob", null );
		
		//FIXME If the max upload file size is reached it should return an error instead of crash
		// FIXME Need to sync the audio/videos
		$transcoder = $this->container->get ( 'imdc_terptube.transcoder' ); // ($this->get('logger'));
		// 		if ($audioFile ==null)
		// 			$mergedFile = $videoFile;
		// 		else
		
		$isFirefox = $request->request->get ( "isFirefox" );
		$finalFile = $isFirefox == 'false' ? $transcoder->mergeAudioVideo ( $audioFile, $videoFile ) : $transcoder->remuxWebM ( $audioFile );
		
		$resourceFile = new ResourceFile ();
		$resourceFile->setMedia ( $media );
		$resourceFile->setWebmExtension ( "webm" );
		$media->setIsReady ( Media::READY_WEBM );
		$resourceFile->setFile ( $finalFile );
		
		$media->setResource ( $resourceFile );
		
		$userObject->addResourceFile ( $media );
		
		$em = $this->container->get ( 'doctrine' )->getManager ();
		
		$em->persist ( $resourceFile );
		$em->persist ( $media );
		
		$em->flush ();
		//FIXME: transcoder seems to do this already. no need to rename and persist
		/*$resource = $media->getResource();
		$resourceFile = new File($resource->getAbsolutePath());
		$fs = new Filesystem();
		$fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
		$resource->setPath("webm");*/
// 		$em->persist ( $resourceFile );
		//$em->flush ();
		
		$eventDispatcher = $this->container->get ( 'event_dispatcher' );
		$uploadedEvent = new UploadEvent ( $media );
		$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
		
		$mediaObjectArray = JSEntities::getMediaObject ( $media );
		
		$return = array (
				'responseCode' => 200,
				'feedback' => 'media added',
				'media' => $mediaObjectArray 
		);
		$return = json_encode ( $return ); // json encode the array
		return new Response ( $return, 200, array (
				'Content-Type' => 'application/json' 
		) );
		
		// return $this->render('IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig');
	}
	public function addSimultaneousRecordingAction(Request $request, $sourceMediaID, $startTime, $url) {
		// FIXME add the recording stuff here
		// throw new NotImplementedException("Not yet implemented");
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$media = new Media ();
		$fileName = $media->getId ();
		$media->setOwner ( $userObject );
		$media->setType ( Media::TYPE_VIDEO );
		$currentTime = new \DateTime ( 'now' );
		$media->setTitle ( "Recording-from-source" . $currentTime->format ( 'Y-m-d-H:i' ) );
		
		$audioFile = $request->files->get ( "audio-blob", null );
		$videoFile = $request->files->get ( "video-blob", null );
		
		//FIXME If the max upload file size is reached it should return an error instead of crash
		// FIXME Need to sync the audio/videos
		$transcoder = $this->container->get ( 'imdc_terptube.transcoder' ); // ($this->get('logger'));
// 		if ($audioFile ==null)
// 			$mergedFile = $videoFile;
// 		else
		$mergedFile = $transcoder->mergeAudioVideo ( $audioFile, $videoFile );
		$resourceFile = new ResourceFile ();
		$resourceFile->setMedia ( $media );
		$resourceFile->setWebmExtension ( "webm" );
		$media->setIsReady ( Media::READY_WEBM );
		$resourceFile->setFile ( $mergedFile );
		
		$media->setResource ( $resourceFile );
		
		$userObject->addResourceFile ( $media );
		
		$em = $this->container->get ( 'doctrine' )->getManager ();
		
		$sourceMedia = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $sourceMediaID );
		$compoundMedia = new CompoundMedia ();
		$compoundMedia->setSource ( $sourceMedia );
		$compoundMedia->setTarget ( $media );
		$compoundMedia->setTargetStartTime ( $startTime );
		$compoundMedia->setType ( 0 ); // Simultaneous Recording
		
		$em->persist ( $resourceFile );
		$em->persist ( $media );
		$em->persist ( $compoundMedia );
		
		$em->flush ();
		
		$resource = $media->getResource();
		$resourceFile = new File($resource->getAbsolutePath());
		$fs = new Filesystem();
		$fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
		$resource->setPath("webm");
// 		$em->persist ( $resourceFile );
		$em->flush ();
		
		$eventDispatcher = $this->container->get ( 'event_dispatcher' );
		$uploadedEvent = new UploadEvent ( $media );
		$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
		
		// $mediaObjectArray = JSEntities::getMediaObject($media);
		$compoundMediaObjectArray = JSEntities::getCompoundMediaObject ( $compoundMedia );
		
		$return = array (
				'responseCode' => 200,
				'feedback' => 'media added',
				'compoundMedia' => $compoundMediaObjectArray 
		);
		$return = json_encode ( $return ); // json encode the array
		return new Response ( $return, 200, array (
				'Content-Type' => 'application/json' 
		) );
		
		// return $this->render('IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig');
	}
	public function addOtherAction(Request $request, $url) {
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		$otherMedia = new Media ();
		
		$formFactory = $this->container->get ( 'form.factory' );
		
		$form = $formFactory->create ( new OtherMediaFormType (), $otherMedia, array () );
		
		$prefix = "";
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		
		if ('POST' === $request->getMethod ()) {
			$form->bind ( $request );
			
			if ($form->isValid ()) {
				$otherMedia->setOwner ( $userObject );
				$otherMedia->setType ( Media::TYPE_OTHER );
				// flush object to database
				$em = $this->container->get ( 'doctrine' )->getManager ();
				$em->persist ( $otherMedia );
				// Remove old avatar from DB:
				$userObject->addResourceFile ( $otherMedia );
				
				$em->flush ();
				
				$this->container->get ( 'session' )->getFlashBag ()->add ( 'media', 'File uploaded successfully!' );
				
				$eventDispatcher = $this->container->get ( 'event_dispatcher' );
				$uploadedEvent = new UploadEvent ( $otherMedia );
				$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				
				// $uploadedEvent->getResponse();
				if ($request->isXmlHttpRequest ()) {
					$response = array (
							'page' => null,
							'finished' => true,
							'media' => JSEntities::getMediaObject ( $otherMedia )
					);
					$response = json_encode ( $response ); // json encode the array
					return new Response ( $response, 200, array (
							'Content-Type' => 'application/json' 
					) );
				} else if ($url === null) {
					$response = new RedirectResponse ( $this->generateUrl ( 'imdc_files_gateway' ) );
				} else {
					$response = new RedirectResponse ( $url );
				}
				return $response;
			}
		}
		$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
				'form' => $form->createView (),
				'postUrl' => $this->generateUrl ( 'imdc_files_gateway_other' )
		) );
		// form not valid, show the basic form
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false 
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		return $response;
	}
}
