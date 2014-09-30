<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use IMDC\TerpTubeBundle\Model\JSEntities;
use FFMpeg\FFProbe;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use IMDC\TerpTubeBundle\Entity\CompoundMedia;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Form\Type\OtherMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\VideoMediaFormType;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Form\Type\AudioMediaFormType;
use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class MyFilesGatewayController extends Controller
{
	const FEEDBACK_MESSAGE_NOT_OWNER = "Not the rightful owner";
	const FEEDBACK_MESSAGE_NOT_EXIST_MEDIA = "Media does not exist";
	const FEEDBACK_MESSAGE_NOT_EXIST_USER = "User does not exist";

	public function listAction(Request $request)
	{
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $resourceFiles = $this->getUser()->getResourceFiles();

        if (!$request->isXmlHttpRequest()) {
            $paginator = $this->get('knp_paginator');
            $resourceFiles = $paginator->paginate(
                $resourceFiles,
                $this->get('request')->query->get('page', 1), /*page number*/
                25 /*limit per page*/
            );
        }

        $parameters = array(
            'resourceFiles' => $resourceFiles
        );

        if (!$request->isXmlHttpRequest()) {
            $parameters['uploadForms'] = MyFilesGatewayController::getUploadForms($this);
        }

        $response = $this->render('IMDCTerpTubeBundle:_MyFiles:'.($request->isXmlHttpRequest() ? 'ajax.' : '').'index.html.twig', $parameters);

        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode(array(
                'page' => $response->getContent(),
                'finished' => false
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        return $response;
	}
	
	/**
	 * A gateway form for uploading/recording or selecting existing files
	 *
	 * @param String $filter        	
	 * @param boolean $isAjax        	
	 * @param String $path        	
	 * @throws AccessDeniedException
	 * @throws NotFoundHttpException
	 * @throws BadRequestHttpException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function gatewayInterpretationsAction(Request $request)
	{
		if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		// CompoundMedia interpretations
		$repo = $em->getRepository('IMDCTerpTubeBundle:CompoundMedia');
		$interpretations = $repo->findAllInterpretationsCreatedByUser($user);
		return $this->render('IMDCTerpTubeBundle:MyFilesGateway:interpretations.html.twig', array (
				'interpretations' => $interpretations 
		));
	}

    public function previewMediaAction(Request $request, $mediaId)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }

        $em = $this->getDoctrine()->getManager();
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            throw new EntityNotFoundException('media not found');
        }

        // FIXME Should check for file permissions before showing the media to the user

        $template = 'IMDCTerpTubeBundle:_Media:'.($request->isXmlHttpRequest() ? 'ajax.' : '').'preview%s.html.twig';
        switch ($media->getType()) {
            case Media::TYPE_AUDIO:
                $template = sprintf($template, 'Audio');
                break;
            case Media::TYPE_VIDEO:
                $template = sprintf($template, 'Video');
                break;
            case Media::TYPE_IMAGE:
                $template = sprintf($template, 'Image');
                break;
            case Media::TYPE_OTHER:
                $template = sprintf($template, 'Other');
                break;
        }

        return new Response(json_encode(array(
            'finished' => false,
            'media' => JSEntities::getMediaObject($media),
            'page' => $this->renderView($template, array(
                    'mediaFile' => $media))
        )), 200, array('Content-Type' => 'application/json'));
    }
	
	public function previewCompoundMediaAction(Request $request, $compoundMediaId, $url)
	{
		$recorderConfiguration = $request->get("recorderConfiguration");
		$user = $this->getUser();
		if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		
		$em = $this->container->get('doctrine')->getManager();
		$mediaFile = $em->getRepository('IMDCTerpTubeBundle:CompoundMedia')->find($compoundMediaId);
		if ($userObject == null)
		{
			throw new NotFoundHttpException(MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_USER);
		}
		
		if (! $request->isXmlHttpRequest())
			return $this->render('IMDCTerpTubeBundle:MyFilesGateway:previewCompoundMedia.html.twig', array (
					"compoundMedia" => $mediaFile 
			));
		else
			$response = $this->render('IMDCTerpTubeBundle:MyFilesGateway:ajax.previewCompoundMedia.html.twig', array (
					"compoundMedia" => $mediaFile 
			));
			// FIXME need to fix the ajax css file to work better.
		$return = array (
				'page' => $response->getContent(),
				'finished' => false 
		);
		$return = json_encode($return); // json encode the array
		$response = new Response($return, 200, array (
				'Content-Type' => 'application/json' 
		));
		return $response;
	}

	/**
	 * An Ajax function to trim a media with a specific media ID, start and end times
	 *
	 * @param Request $request
	 * @param Media $mediaId
	 * @param
	 *        	$startTime
	 * @param
	 *        	$endTime
	 */
	public function trimMediaAction(Request $request, $mediaId, $startTime, $endTime)
	{
		if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		if (! $request->isXmlHttpRequest())
			throw new BadRequestHttpException('Only Ajax POST calls accepted');
		$user = $this->getUser();
		$em = $this->get('doctrine')->getManager();
		/**
		 *
		 * @var $media IMDC\TerpTubeBundle\Entity\Media
		 */
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
		
		if ($media == null)
		{
			$return = array (
					'responseCode' => 400,
					'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
			);
		}
		else if ($media->getOwner() != $user)
		{
			$return = array (
					'responseCode' => 400,
					'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
			);
		}
		else
		{
			// FIXME if video is being transcoded, need to queue the operation to execute once it completes
			// FIXME check if start/end times are proper values
			$resourceFile = $media->getResource();
			$webmFile = $resourceFile->getAbsolutePathWebm();
			$mp4File = $resourceFile->getAbsolutePath();
			$ffprobe = FFProbe::create ();
			$metaData = $media->getMetaData();
			$transcoder = $this->container->get('imdc_terptube.transcoder');
			// FIXME Throws exception at rename when trying to move the mp4 file.
			if ($media->getIsReady() == Media::READY_YES)
			{
				$resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
				$resultMp4 = $transcoder->trimVideo($mp4File, $startTime, $endTime);
				$finalFile = new File($webmFile);
				$videoDuration = $ffprobe->streams ( $finalFile->getRealPath () )->videos ()->first ()->get ( 'duration' );
				$fileSize = filesize ( $finalFile->getRealPath () );
				$metaData->setDuration ( $videoDuration );
				$metaData->setSize ( $fileSize );
				$em->flush();
				if ($resultWebM && $resultMp4)
				{
					$return = array (
							'responseCode' => 200,
							'feedback' => 'Successfully trimmed media!',
							'media' => JSEntities::getMediaObject($media) 
					);
				}
				else
				{
					$return = array (
							'responseCode' => 400,
							'feedback' => 'Trimming media failed!.' 
					);
				}
			}
			else if ($media->getIsReady() == Media::READY_WEBM)
			{
				// FIXME this will encode a second time since the video was already queued for transcoding
				// FIXME need to find out how to dequeue an item from the RabbitMQ queue
				$resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
				$pendingOperations = $media->getPendingOperations();
				if ($pendingOperations == null)
					$pendingOperations = array ();
				array_push($pendingOperations, "trim,mp4," . $startTime . "," . $endTime);
				$media->setPendingOperations($pendingOperations);
				$finalFile = new File($webmFile);
				$videoDuration = $ffprobe->streams ( $finalFile->getRealPath () )->videos ()->first ()->get ( 'duration' );
				$fileSize = filesize ( $finalFile->getRealPath () );
				$metaData->setDuration ( $videoDuration );
				$metaData->setSize ( $fileSize );
				$em->flush();
				// $eventDispatcher = $this->container->get ( 'event_dispatcher' );
				// $uploadedEvent = new UploadEvent ( $media );
				// $eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				if ($resultWebM)
				{
					$return = array (
							'responseCode' => 200,
							'feedback' => 'Successfully trimmed media!',
							'media' => JSEntities::getMediaObject($media) 
					);
				}
				else
				{
					$return = array (
							'responseCode' => 400,
							'feedback' => 'Trimming media failed!.' 
					);
				}
			}
			else
			{
				
				$return = array (
						'responseCode' => 400,
						'feedback' => 'This should not happen!' 
				);
			}
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array (
				'Content-Type' => 'application/json' 
		));
	}

    public function updateMediaAction(Request $request, $mediaId)
    {
        if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (! $request->isXmlHttpRequest())
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        /**
         *
         * @var $media IMDC\TerpTubeBundle\Entity\Media
         */
        $mediaToUpdate = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);

        if ($mediaToUpdate == null)
        {
            $return = array (
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        }
        else if ($mediaToUpdate->getOwner() != $user)
        {
            $return = array (
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
            );
        }
        else
        {
            $media = json_decode($request->get('media'), true);
            if ($mediaToUpdate !== null && $media != null && $media ['title'] !== null)
            {
                $mediaToUpdate->setTitle($media ['title']);
                $em->flush();
                $return = array (
                    'responseCode' => 200,
                    'feedback' => 'Successfully removed media!'
                );
            }
            else
            {
                $return = array (
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
                );
            }
        }
        $return = json_encode($return); // json encode the array
        return new Response($return, 200, array (
            'Content-Type' => 'application/json'
        ));
    }
	
	/**
	 * An Ajax function that deletes a media with a specific media ID
	 *
	 * @param Request $request        	
	 * @param unknown_type $mediaId        	
	 */
	public function deleteMediaAction(Request $request, $mediaId)
	{
		if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		if (! $request->isXmlHttpRequest())
			throw new BadRequestHttpException('Only Ajax POST calls accepted');
		$user = $this->getUser();
		$em = $this->get('doctrine')->getManager();
		/**
		 *
		 * @var $media IMDC\TerpTubeBundle\Entity\Media
		 */
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
		// FIXME need to figure out if video is being transcoded and interrupt it if so and clean up
		if ($media !== null)
		{
			if ($media->getOwner() != $user)
			{
				$return = array (
						'responseCode' => 400,
						'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
				);
			}
			else {
				$em->remove($media);
				$em->flush();
				$return = array (
						'responseCode' => 200,
						'feedback' => 'Successfully removed media!' 
				);
			}
		}
		else
		{
			$return = array (
					'responseCode' => 400,
					'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
			);
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array (
				'Content-Type' => 'application/json' 
		));
	}

    public function addRecordingAction(Request $request, $url) {
        if (!$request->isMethod('POST')) {
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
            //$response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'recordVideo.html.twig', array (
            $response = $this->render ( 'IMDCTerpTubeBundle:_Media:' . $prefix . 'recordVideo.html.twig', array (
                "recorderConfiguration" => $recorderConfiguration,
                'isPost' => $this->get('request')->get('isPost', false)
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

        // FIXME If the max upload file size is reached it should return an error instead of crash
        // FIXME Need to sync the audio/videos
        $transcoder = $this->container->get ( 'imdc_terptube.transcoder' ); // ($this->get('logger'));
        // if ($audioFile ==null)
        // $mergedFile = $videoFile;
        // else

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
        // FIXME: transcoder seems to do this already. no need to rename and persist
        // Need to rename to webm since in some cases the recording is done as a .bin file
        $resource = $media->getResource ();
        $resourceFile = new File ( $resource->getAbsolutePath () );
        $targetFile = $resource->getUploadRootDir () . '/' . $resource->getId () . '.webm';
        if (! file_exists ( $targetFile )) {
            $fs = new Filesystem ();
            $fs->rename ( $resourceFile, $resource->getUploadRootDir () . '/' . $resource->getId () . '.webm' );
        }
        $resource->setPath ( "webm" );
        // $em->persist ( $resourceFile );
        $em->flush ();

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
        if (!$request->isMethod('POST')) {
            $recorderConfiguration = $request->get("recorderConfiguration");
            $user = $this->getUser();
            if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
            {
                return $this->redirect($this->generateUrl('fos_user_security_login'));
            }
            $userManager = $this->container->get('fos_user.user_manager');
            $userObject = $userManager->findUserByUsername($user->getUsername());

            $em = $this->container->get('doctrine')->getManager();
            $mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')->find($sourceMediaID);
            if ($userObject == null)
            {
                throw new NotFoundHttpException("This user does not exist");
            }
            return $this
                ->render('IMDCTerpTubeBundle:MediaController:simultaneousPreviewAndRecord.html.twig',
                    array("mediaFile" => $mediaFile));
        }

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

        // FIXME If the max upload file size is reached it should return an error instead of crash
        // FIXME Need to sync the audio/videos
        $transcoder = $this->container->get ( 'imdc_terptube.transcoder' ); // ($this->get('logger'));
        // if ($audioFile ==null)
        // $mergedFile = $videoFile;
        // else
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

        $resource = $media->getResource ();
        $resourceFile = new File ( $resource->getAbsolutePath () );
        $fs = new Filesystem ();
        $fs->rename ( $resourceFile, $resource->getUploadRootDir () . '/' . $resource->getId () . '.webm' );
        $resource->setPath ( "webm" );
        // $em->persist ( $resourceFile );
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
                    $response = new RedirectResponse ( $this->generateUrl ( 'imdc_myfiles_list' ) );
                } else {
                    $response = new RedirectResponse ( $url );
                }
                return $response;
            }
        }
        $response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
            'form' => $form->createView (),
            'postUrl' => $this->generateUrl ( 'imdc_myfiles_add_audio' )
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
                    $response = new RedirectResponse ( $this->generateUrl ( 'imdc_myfiles_list' ) );
                } else {
                    $response = new RedirectResponse ( $url );
                }
                return $response;
            }
        }
        $response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
            'form' => $form->createView (),
            'postUrl' => $this->generateUrl ( 'imdc_myfiles_add_video' )
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
                    $response = new RedirectResponse ( $this->generateUrl ( 'imdc_myfiles_list' ) );
                } else {
                    $response = new RedirectResponse ( $url );
                }
                return $response;
            }
        }
        $response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
            'form' => $form->createView (),
            'postUrl' => $this->generateUrl ( 'imdc_myfiles_add_image' )
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
                    $response = new RedirectResponse ( $this->generateUrl ( 'imdc_myfiles_list' ) );
                } else {
                    $response = new RedirectResponse ( $url );
                }
                return $response;
            }
        }
        $response = $this->render ( 'IMDCTerpTubeBundle:AddFileGateway:' . $prefix . 'addFile.html.twig', array (
            'form' => $form->createView (),
            'postUrl' => $this->generateUrl ( 'imdc_myfiles_add_other' )
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

    public static function getUploadForms(Controller $controller) {
        $formAudio = $controller->createForm(new AudioMediaFormType(), new Media());
        $formVideo = $controller->createForm(new VideoMediaFormType(), new Media());
        $formImage = $controller->createForm(new ImageMediaFormType(), new Media());
        $formOther = $controller->createForm(new OtherMediaFormType(), new Media());

        return array(
            $formAudio->createView(),
            $formVideo->createView(),
            $formImage->createView(),
            $formOther->createView()
        );
    }
}
