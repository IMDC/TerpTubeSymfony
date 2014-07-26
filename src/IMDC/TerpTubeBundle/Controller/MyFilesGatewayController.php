<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
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
use IMDC\TerpTubeBundle\Model\JSEntities;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use IMDC\TerpTubeBundle\Entity\Post;

class MyFilesGatewayController extends Controller {
	
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
	public function gatewayAction(Request $request) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$user = $this->getUser ();
		$resourceFiles = $user->getResourceFiles ();
		$formAudio = $this->createForm ( new AudioMediaFormType (), new Media (), array () );
		$formVideo = $this->createForm ( new VideoMediaFormType (), new Media (), array () );
		$formImage = $this->createForm ( new ImageMediaFormType (), new Media (), array () );
		$formOther = $this->createForm ( new OtherMediaFormType (), new Media (), array () );
		$uploadForms = array ( $formAudio->createView (), $formVideo->createView (), $formImage->createView (), $formOther->createView () );
		return $this->render ( 'IMDCTerpTubeBundle:MyFilesGateway:index.html.twig', array (
				'resourceFiles' => $resourceFiles,
				'uploadForms' => $uploadForms
		) );
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
	public function gatewayInterpretationsAction(Request $request) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$user = $this->getUser ();
		$em = $this->getDoctrine ()->getManager ();
		// CompoundMedia interpretations
		$repo = $em->getRepository ( 'IMDCTerpTubeBundle:CompoundMedia' );
		$interpretations = $repo->findAllInterpretationsCreatedByUser ( $user );
		return $this->render ( 'IMDCTerpTubeBundle:MyFilesGateway:interpretations.html.twig', array (
				'interpretations' => $interpretations 
		) );
	}
	public function previewCompoundMediaAction(Request $request, $compoundMediaId, $url) {
		$recorderConfiguration = $request->get ( "recorderConfiguration" );
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		
		$em = $this->container->get ( 'doctrine' )->getManager ();
		$mediaFile = $em->getRepository ( 'IMDCTerpTubeBundle:CompoundMedia' )->find ( $compoundMediaId );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		
		if (! $request->isXmlHttpRequest ())
			return $this->render ( 'IMDCTerpTubeBundle:MyFilesGateway:previewCompoundMedia.html.twig', array (
					"compoundMedia" => $mediaFile 
			) );
		else
			$response = $this->render ( 'IMDCTerpTubeBundle:MyFilesGateway:ajax.previewCompoundMedia.html.twig', array (
					"compoundMedia" => $mediaFile 
			) );
			// FIXME need to fix the ajax css file to work better.
		$return = array (
				'page' => $response->getContent (),
				'finished' => false 
		);
		$return = json_encode ( $return ); // json encode the array
		$response = new Response ( $return, 200, array (
				'Content-Type' => 'application/json' 
		) );
		return $response;
	}
	
	/**
	 * An Ajax function to trim a media with a specific media ID, start and end times
	 *
	 * @param Request $request        	
	 * @param Media $mediaId        	
	 * @param $startTime
	 * @param $endTime
	 */
	public function trimMediaAction(Request $request, $mediaId, $startTime, $endTime) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		if (! $request->isXmlHttpRequest ())
			throw new BadRequestHttpException ( 'Only Ajax POST calls accepted' );
		$user = $this->getUser ();
		$em = $this->get ( 'doctrine' )->getManager ();
		/**
		 *
		 * @var $media IMDC\TerpTubeBundle\Entity\Media
		 */
		$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
		if ($media->getOwner () != $user) {
			$return = array (
					'responseCode' => 400,
					'feedback' => 'Not the rightful owner of the file' 
			);
		} else {
			// FIXME if video is being transcoded, need to queue the operation to execute once it completes
			// FIXME check if start/end times are proper values
			$resourceFile = $media->getResource ();
			$webmFile = $resourceFile->getAbsolutePathWebm ();
			$mp4File = $resourceFile->getAbsolutePath();
			
			$transcoder = $this->container->get ( 'imdc_terptube.transcoder' ); // ($this->get('logger'));
			//FIXME Throws exception at rename when trying to move the mp4 file.
			if ($media->getIsReady () == Media::READY_YES) {
				$resultWebM = $transcoder->trimVideo ( $webmFile, $startTime, $endTime );
				$resultMp4 = $transcoder->trimVideo ( $mp4File, $startTime, $endTime );
				if ($resultWebM && $resultMp4) {
					$return = array (
							'responseCode' => 200,
							'feedback' => 'Successfully trimmed media!' 
					);
				} else {
					$return = array (
							'responseCode' => 400,
							'feedback' => 'Trimming media failed!.' 
					);
				}
			}
			else if ($media->getIsReady() == Media::READY_WEBM) 
			{
				//FIXME this will encode a second time since the video was already queued for transcoding
				//FIXME need to find out how to dequeue an item from the RabbitMQ queue
				$resultWebM = $transcoder->trimVideo ( $webmFile, $startTime, $endTime );
				$pendingOperations = $media->getPendingOperations();
				if ($pendingOperations == null)
					$pendingOperations = array();
				array_push($pendingOperations, "trim,mp4,".$startTime.",".$endTime);
				$media->setPendingOperations($pendingOperations);
				$em->flush ();
// 				$eventDispatcher = $this->container->get ( 'event_dispatcher' );
// 				$uploadedEvent = new UploadEvent ( $media );
// 				$eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
				if ($resultWebM) {
					$return = array (
							'responseCode' => 200,
							'feedback' => 'Successfully trimmed media!'
					);
				} else {
					$return = array (
							'responseCode' => 400,
							'feedback' => 'Trimming media failed!.'
					);
				}
			}
			else {
				
				$return = array (
						'responseCode' => 400,
						'feedback' => 'This should not happen!' 
				);
			}
		}
		$return = json_encode ( $return ); // json encode the array
		return new Response ( $return, 200, array (
				'Content-Type' => 'application/json' 
		) );
	}
	
	/**
	 * An Ajax function that deletes a media with a specific media ID
	 *
	 * @param Request $request        	
	 * @param unknown_type $mediaId        	
	 */
	public function deleteMediaAction(Request $request, $mediaId) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		if (! $request->isXmlHttpRequest ())
			throw new BadRequestHttpException ( 'Only Ajax POST calls accepted' );
		$user = $this->getUser ();
		$em = $this->get ( 'doctrine' )->getManager ();
		/**
		 *
		 * @var $media IMDC\TerpTubeBundle\Entity\Media
		 */
		$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
		
		if ($media->getOwner () != $user) {
			$return = array (
					'responseCode' => 400,
					'feedback' => 'Not the rightful owner of the file' 
			);
		} else {
			// FIXME need to figure out if video is being transcoded and interrupt it if so and clean up
			if ($media !== null) {
				$em->remove ( $media );
				$em->flush ();
				$return = array (
						'responseCode' => 200,
						'feedback' => 'Successfully removed media!' 
				);
			} else {
				$return = array (
						'responseCode' => 400,
						'feedback' => 'Could not remove media, or media does not exist.' 
				);
			}
		}
		$return = json_encode ( $return ); // json encode the array
		return new Response ( $return, 200, array (
				'Content-Type' => 'application/json' 
		) );
	}
	
	/**
	 * An Ajax function that previews a media with a specific media ID
	 *
	 * @param Request $request        	
	 * @param unknown_type $mediaId        	
	 */
	public function previewMediaAction(Request $request, $mediaId) {
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		if (! $request->isXmlHttpRequest ())
			throw new BadRequestHttpException ( 'Only Ajax POST calls accepted' );
		$user = $this->getUser ();
		$em = $this->get ( 'doctrine' )->getManager ();
		/**
		 *
		 * @var $media IMDC\TerpTubeBundle\Entity\Media
		 */
		$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
		
		$responseURL = "";
		
		if ($request->isXmlHttpRequest ()) {
			$prefix = "ajax.";
		}
		// form not valid, show the basic form
		if ($media !== null) {
			// FIXME Should check for file permissions before showing the media to the user
			switch ($media->getType ()) {
				case Media::TYPE_AUDIO :
					$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'previewAudio.html.twig';
					break;
				case Media::TYPE_VIDEO :
					$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'previewVideo.html.twig';
					break;
				case Media::TYPE_IMAGE :
					$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'previewImage.html.twig';
					break;
				case Media::TYPE_AUDIO :
					$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'previewAudio.html.twig';
					break;
				case Media::TYPE_OTHER :
					$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'previewOther.html.twig';
					break;
			}
		} else {
			throw new EntityNotFoundException ( "Cannot find media with that ID" );
		}
		$response = $this->render ( $responseURL, array (
				'mediaFile' => $media 
		) );
		
		if ($request->isXmlHttpRequest ()) {
			$return = array (
					'page' => $response->getContent (),
					'finished' => false,
					'media' => JSEntities::getMediaObject ( $media )
			);
			$return = json_encode ( $return ); // json encode the array
			$response = new Response ( $return, 200, array (
					'Content-Type' => 'application/json' 
			) );
		}
		
		return $response;
	}
	public function recordMediaAction(Request $request, $url) {
		// throw new NotImplementedException("Not yet implemented");
		$recorderConfiguration = $request->get ( "recorderConfiguration" );
		$user = $this->getUser ();
		if (! $this->container->get ( 'imdc_terptube.authentication_manager' )->isAuthenticated ( $request )) {
			return $this->redirect ( $this->generateUrl ( 'fos_user_security_login' ) );
		}
		$userManager = $this->container->get ( 'fos_user.user_manager' );
		$userObject = $userManager->findUserByUsername ( $user->getUsername () );
		if ($userObject == null) {
			throw new NotFoundHttpException ( "This user does not exist" );
		}
		return $this->render ( 'IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig', array (
				"recorderConfiguration" => $recorderConfiguration 
		) );
	}
}
