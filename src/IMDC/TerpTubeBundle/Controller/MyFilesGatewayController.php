<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityNotFoundException;
use IMDC\TerpTubeBundle\Entity\Interpretation;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Form\Type\ResourceFileFormType;
use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
use Symfony\Component\Process\Process;
use IMDC\TerpTubeBundle\Entity\Post;

class MyFilesGatewayController extends Controller
{
    const FEEDBACK_MESSAGE_NOT_OWNER = "Not the rightful owner";
    const FEEDBACK_MESSAGE_NOT_EXIST_MEDIA = "Media does not exist";
    const FEEDBACK_MESSAGE_NOT_EXIST_USER = "User does not exist";
    const FEEDBACK_MESSAGE_MEDIA_IN_USE = "Media in use";
    const FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS = "Successfully removed media!";

    public function listAction(Request $request)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $criteria = Criteria::create();
        $type = $this->get('request')->query->get('type', false);
        $style = $this->get('request')->query->get('style', 'grid');

        if ($type !== false) {
            $criteria->andWhere(Criteria::expr()->eq('type', $type));
        }

        $resourceFiles = $this->getUser()->getResourceFiles()->matching($criteria);

        $paginator = $this->get('knp_paginator');
        $resourceFiles = $paginator->paginate(
            $resourceFiles,
            $this->get('request')->query->get('page', 1), /*page number*/
            !$request->isXmlHttpRequest() ? 24 : 12 /*limit per page*/
        );

        $parameters = array(
            'resourceFiles' => $resourceFiles,
            'style' => $style
        );

        if (!$request->isXmlHttpRequest()) {
            $parameters ['uploadForm'] = $this->createForm(new MediaType())->createView();
        }

        $response = $this->render('IMDCTerpTubeBundle:MyFiles:' .
            ($request->isXmlHttpRequest() ? 'list.' . $style : 'index') . '.html.twig', $parameters);

        if ($request->isXmlHttpRequest()) {
            $response = new Response (json_encode(array(
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
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        // CompoundMedia interpretations
        $repo = $em->getRepository('IMDCTerpTubeBundle:CompoundMedia');
        $interpretations = $repo->findAllInterpretationsCreatedByUser($user);
        return $this->render('IMDCTerpTubeBundle:MyFilesGateway:interpretations.html.twig', array(
            'interpretations' => $interpretations
        ));
    }

    public function previewMediaAction(Request $request, $mediaId)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException ('Only Ajax POST calls accepted');
        }

        $em = $this->getDoctrine()->getManager();
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            throw new EntityNotFoundException ('media not found');
        }

        // FIXME Should check for file permissions before showing the media to the user

        $template = 'IMDCTerpTubeBundle:Media:' . ($request->isXmlHttpRequest() ? 'ajax.' : '') . 'preview%s.html.twig';
        switch ($media->getType()) {
            case Media::TYPE_AUDIO :
                $template = sprintf($template, 'Audio');
                break;
            case Media::TYPE_VIDEO :
                $template = sprintf($template, 'Video');
                break;
            case Media::TYPE_IMAGE :
                $template = sprintf($template, 'Image');
                break;
            case Media::TYPE_OTHER :
                $template = sprintf($template, 'Other');
                break;
        }

        return new Response (json_encode(array(
            'finished' => false,
            'media' => JSEntities::getMediaObject($media),
            'page' => $this->renderView($template, array(
                    'mediaFile' => $media
                ))
        )), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function previewCompoundMediaAction(Request $request, $compoundMediaId, $url)
    {
        $recorderConfiguration = $request->get("recorderConfiguration");
        $user = $this->getUser();
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $userManager = $this->container->get('fos_user.user_manager');
        $userObject = $userManager->findUserByUsername($user->getUsername());

        $em = $this->container->get('doctrine')->getManager();
        $mediaFile = $em->getRepository('IMDCTerpTubeBundle:CompoundMedia')->find($compoundMediaId);
        if ($userObject == null) {
            throw new NotFoundHttpException (MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_USER);
        }

        if (!$request->isXmlHttpRequest())
            return $this->render('IMDCTerpTubeBundle:MyFilesGateway:previewCompoundMedia.html.twig', array(
                "compoundMedia" => $mediaFile
            ));
        else
            $response = $this->render('IMDCTerpTubeBundle:MyFilesGateway:ajax.previewCompoundMedia.html.twig', array(
                "compoundMedia" => $mediaFile
            ));
        // FIXME need to fix the ajax css file to work better.
        $return = array(
            'page' => $response->getContent(),
            'finished' => false
        );
        $return = json_encode($return); // json encode the array
        $response = new Response ($return, 200, array(
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
     *            $startTime
     * @param
     *            $endTime
     */
    public function trimMediaAction(Request $request, $mediaId, $startTime, $endTime)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (!$request->isXmlHttpRequest())
            throw new BadRequestHttpException ('Only Ajax POST calls accepted');
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        /**
         *
         * @var $media IMDC\TerpTubeBundle\Entity\Media
         */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);

        if ($media == null) {
            $return = array(
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        } else if ($media->getOwner() != $user) {
            $return = array(
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
            );
        } else {
            // FIXME if video is being transcoded, need to queue the operation to execute once it completes
            // FIXME check if start/end times are proper values
            $resourceFile = $media->getResource();
            $webmFile = $resourceFile->getAbsolutePathWebm();
            $mp4File = $resourceFile->getAbsolutePath();
            $ffprobe = FFProbe::create();
            $metaData = $media->getMetaData();
            $transcoder = $this->container->get('imdc_terptube.transcoder');
            // FIXME Throws exception at rename when trying to move the mp4 file.
            if ($media->getIsReady() == Media::READY_YES) {
                $resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
                $resultMp4 = $transcoder->trimVideo($mp4File, $startTime, $endTime);
                $finalFile = new File ($webmFile);
                $videoDuration = $ffprobe->streams($finalFile->getRealPath())->videos()->first()->get('duration');
                $fileSize = filesize($finalFile->getRealPath());
                $metaData->setDuration($videoDuration);
                $metaData->setSize($fileSize);
                $em->flush();
                if ($resultWebM && $resultMp4) {
                    $return = array(
                        'responseCode' => 200,
                        'feedback' => 'Successfully trimmed media!',
                        'media' => JSEntities::getMediaObject($media)
                    );
                } else {
                    $return = array(
                        'responseCode' => 400,
                        'feedback' => 'Trimming media failed!.'
                    );
                }
            } else if ($media->getIsReady() == Media::READY_WEBM) {
                // FIXME this will encode a second time since the video was already queued for transcoding
                // FIXME need to find out how to dequeue an item from the RabbitMQ queue
                $resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
                $pendingOperations = $media->getPendingOperations();
                if ($pendingOperations == null)
                    $pendingOperations = array();
                array_push($pendingOperations, "trim,mp4," . $startTime . "," . $endTime);
                $media->setPendingOperations($pendingOperations);
                $finalFile = new File ($webmFile);
                $videoDuration = $ffprobe->streams($finalFile->getRealPath())->videos()->first()->get('duration');
                $fileSize = filesize($finalFile->getRealPath());
                $metaData->setDuration($videoDuration);
                $metaData->setSize($fileSize);
                $em->flush();
                // $eventDispatcher = $this->container->get ( 'event_dispatcher' );
                // $uploadedEvent = new UploadEvent ( $media );
                // $eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
                if ($resultWebM) {
                    $return = array(
                        'responseCode' => 200,
                        'feedback' => 'Successfully trimmed media!',
                        'media' => JSEntities::getMediaObject($media)
                    );
                } else {
                    $return = array(
                        'responseCode' => 400,
                        'feedback' => 'Trimming media failed!.'
                    );
                }
            } else {

                $return = array(
                    'responseCode' => 400,
                    'feedback' => 'This should not happen!'
                );
            }
        }
        $return = json_encode($return); // json encode the array
        return new Response ($return, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function updateMediaAction(Request $request, $mediaId)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (!$request->isXmlHttpRequest())
            throw new BadRequestHttpException ('Only Ajax POST calls accepted');
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        /**
         *
         * @var $media IMDC\TerpTubeBundle\Entity\Media
         */
        $mediaToUpdate = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);

        if ($mediaToUpdate == null) {
            $return = array(
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        } else if ($mediaToUpdate->getOwner() != $user) {
            $return = array(
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
            );
        } else {
            $media = json_decode($request->get('media'), true);
            if ($mediaToUpdate !== null && $media != null && $media ['title'] !== null) {
                $mediaToUpdate->setTitle($media ['title']);
                $em->flush();
                $return = array(
                    'responseCode' => 200,
                    'feedback' => 'Successfully updated media!'
                );
            } else {
                $return = array(
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
                );
            }
        }
        $return = json_encode($return); // json encode the array
        return new Response ($return, 200, array(
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
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (!$request->isXmlHttpRequest())
            throw new BadRequestHttpException ('Only Ajax POST calls accepted');
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        /**
         *
         * @var $media IMDC\TerpTubeBundle\Entity\Media
         */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        // FIXME need to figure out if video is being transcoded and interrupt it if so and clean up
        // FIXME need to check if the video is used as a post somewhere and ask the user to confirm before deleting
        if ($media !== null) {
            if ($media->getOwner() != $user) {
                $return = array(
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
                );
            } else {
                $needsConfirmation = false;

                // Find all places where the media can be used.
                // Also the interpretations
                //TODO this should be moved to its own method somewhere.
                $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getForumsForMedia($media);
                $messages = $em->getRepository('IMDCTerpTubeBundle:Message')->getMessagesForMedia($media);
                $posts = $em->getRepository('IMDCTerpTubeBundle:Post')->getPostsForMedia($media);
                $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getThreadsForMedia($media);

                $mediaInUse = array();
                if (count($forums) > 0) {
                    $needsConfirmation = true;
                    $mediaInUse [] = 'forum';
                }
                if (count($messages) > 0) {
                    $needsConfirmation = true;
                    $mediaInUse [] = 'message';
                }
                if (count($posts) > 0) {
                    $needsConfirmation = true;
                    $mediaInUse [] = 'post';
                }
                if (count($threads) > 0) {
                    $needsConfirmation = true;
                    $mediaInUse [] = 'thread';
                }
                if ($user->getProfile()->getAvatar() == $media) {
                    $needsConfirmation = true;
                    $mediaInUse [] = 'avatar';
                }

                if ($needsConfirmation) {
                    $confirm = $request->request->get('confirm');
                    $this->get('logger')->info("confirm: " . $confirm);
                    if ($confirm == 'true') {
                        // User has confirmed, remove the media from all the places and then remove it
                        foreach ($forums as $forum) {
                            $forum->removeTitleMedia($media);
                        }
                        foreach ($messages as $message) {
                            $message->removeAttachedMedia($media);
                        }
                        foreach ($posts as $post) {
                            $post->removeAttachedFile($media);
                        }
                        foreach ($threads as $thread) {
                            $thread->removeMediaIncluded($media);
                        }
                        if ($user->getProfile()->getAvatar() == $media) {
                            $user->getProfile()->setAvatar(null);
                        }
                        $em->remove($media);
                        $em->flush();

                        $return = array(
                            'responseCode' => 200,
                            'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
                        );
                    } else {
                        // User has not confirmed, send a confirmation message
                        $return = array(
                            'responseCode' => 400,
                            'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_IN_USE,
                            'mediaInUse' => $mediaInUse
                        );
                    }
                } else {
                    $em->remove($media);
                    $em->flush();
                    $return = array(
                        'responseCode' => 200,
                        'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
                    );
                }
            }
        } else {
            $return = array(
                'responseCode' => 400,
                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        }
        $return = json_encode($return); // json encode the array
        return new Response ($return, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function addRecordingAction(Request $request)
    {
        // post requests only
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('POST requests only');
        }

        // check if user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $user = $this->getUser();
        $currentTime = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();

        $isFirefox = filter_var($request->request->get('isFirefox'), FILTER_VALIDATE_BOOLEAN);
        $video = $request->files->get('video-blob', null);
        $audio = $request->files->get('audio-blob', null);
        if (($isFirefox && empty($audio)) || (!$isFirefox && (empty($video) || empty($audio)))) {
            throw new \Exception('no media data found in request');
        }

        $isInterpretation = filter_var($request->request->get('isInterpretation'), FILTER_VALIDATE_BOOLEAN);
        $sourceStartTime = floatval($request->request->get('sourceStartTime', 0));
        $sourceId = $request->request->get('sourceId', null);
        $sourceMedia = null;
        if ($isInterpretation) {
            $sourceMedia = $em->getRepository('IMDCTerpTubeBundle:Media')->find($sourceId);
            if (!$sourceMedia) {
                throw new \Exception('source media not found');
            }
        }

        $transcoder = $this->container->get('imdc_terptube.transcoder');
        $mergedFile = $isFirefox
            ? $transcoder->remuxWebM($audio)
            : $transcoder->mergeAudioVideo($audio, $video);
        $mergedFile = $transcoder->removeFirstFrame($mergedFile);

        $resourceFile = new ResourceFile();
        $resourceFile->setFile($mergedFile);
        $resourceFile->setWebmExtension('webm');

        if ($isInterpretation) {
            $media = new Interpretation();
            $media->setSourceStartTime($sourceStartTime);
            $media->setSource($sourceMedia);
        } else {
            $media = new Media();
        }

        $media->setOwner($user);
        $media->setType(Media::TYPE_VIDEO);
        $media->setTitle('Recording-' . $currentTime->format('Y-m-d-H:i'));
        $media->setIsReady(Media::READY_WEBM);
        $media->setResource($resourceFile);

        $resourceFile->setMedia($media);
        $user->addResourceFile($media);

        $em->persist($resourceFile);
        $em->persist($media);
        $em->persist($user);
        $em->flush();

        $eventDispatcher = $this->container->get('event_dispatcher');
        $uploadEvent = new UploadEvent($media);
        $eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadEvent);

        $content = array(
            'responseCode' => 200,
            'feedback' => 'media added',
            'media' => JSEntities::getMediaObject($media)
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @deprecated
     */
    public function addRecordingOldAction(Request $request, $url) //TODO delete
    {
        if (!$request->isMethod('POST')) {
            // FIXME add the recording stuff here
            // throw new NotImplementedException("Not yet implemented");
            $user = $this->getUser();
            if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
                return $this->redirect($this->generateUrl('fos_user_security_login'));
            }
            $userManager = $this->container->get('fos_user.user_manager');
            $userObject = $userManager->findUserByUsername($user->getUsername());
            if ($userObject == null) {
                throw new NotFoundHttpException ("This user does not exist");
            }
            $recorderConfiguration = $request->get("recorderConfiguration");
            $prefix = "";
            if ($request->isXmlHttpRequest()) {
                $prefix = "ajax.";
            }
            $response = $this->render('IMDCTerpTubeBundle:Media:' . $prefix . 'recordVideo.html.twig', array(
                "recorderConfiguration" => $recorderConfiguration,
                'isPost' => $this->get('request')->get('isPost', false)
            ));
            // form not valid, show the basic form
            if ($request->isXmlHttpRequest()) {
                $return = array(
                    'page' => $response->getContent(),
                    'finished' => false
                );
                $return = json_encode($return); // json encode the array
                $response = new Response ($return, 200, array(
                    'Content-Type' => 'application/json'
                ));
            }
            return $response;
        }

        // FIXME add the recording stuff here
        // throw new NotImplementedException("Not yet implemented");
        $user = $this->getUser();
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $userManager = $this->container->get('fos_user.user_manager');
        $userObject = $userManager->findUserByUsername($user->getUsername());
        if ($userObject == null) {
            throw new NotFoundHttpException ("This user does not exist");
        }
        $media = new Media ();
        $fileName = $media->getId();
        $media->setOwner($userObject);
        $media->setType(Media::TYPE_VIDEO);
        $currentTime = new \DateTime ('now');
        $media->setTitle("Recording-" . $currentTime->format('Y-m-d-H:i'));

        $audioFile = $request->files->get("audio-blob", null);
        $videoFile = $request->files->get("video-blob", null);

        // FIXME If the max upload file size is reached it should return an error instead of crash
        // FIXME Need to sync the audio/videos
        $transcoder = $this->container->get('imdc_terptube.transcoder'); // ($this->get('logger'));
        // if ($audioFile ==null)
        // $mergedFile = $videoFile;
        // else

        $isFirefox = $request->request->get("isFirefox");
        $finalFile = $isFirefox == 'false' ? $transcoder->mergeAudioVideo($audioFile, $videoFile) : $transcoder->remuxWebM($audioFile);
        $finalFile = $transcoder->removeFirstFrame($finalFile);
        $resourceFile = new ResourceFile ();
        $resourceFile->setMedia($media);
        $resourceFile->setWebmExtension("webm");
        $media->setIsReady(Media::READY_WEBM);
        $resourceFile->setFile($finalFile);

        $media->setResource($resourceFile);

        $userObject->addResourceFile($media);

        $em = $this->container->get('doctrine')->getManager();

        $em->persist($resourceFile);
        $em->persist($media);

        $em->flush();
        // FIXME: transcoder seems to do this already. no need to rename and persist
        // Need to rename to webm since in some cases the recording is done as a .bin file
        $resource = $media->getResource();
        $resourceFile = new File ($resource->getAbsolutePath());
        $targetFile = $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm';
        if (!file_exists($targetFile)) {
            $fs = new Filesystem ();
            $fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
        }
        $resource->setPath("webm");
        // $em->persist ( $resourceFile );
        $em->flush();

        $eventDispatcher = $this->container->get('event_dispatcher');
        $uploadedEvent = new UploadEvent ($media);
        $eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

        $mediaObjectArray = JSEntities::getMediaObject($media);

        $return = array(
            'responseCode' => 200,
            'feedback' => 'media added',
            'media' => $mediaObjectArray
        );
        $return = json_encode($return); // json encode the array
        return new Response ($return, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @deprecated
     */
    public function addSimultaneousRecordingAction(Request $request, $sourceMediaID, $startTime, $url) //TODO delete
    {
        if (!$request->isMethod('POST')) {
            $recorderConfiguration = $request->get("recorderConfiguration");
            $user = $this->getUser();
            if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
                return $this->redirect($this->generateUrl('fos_user_security_login'));
            }
            $userManager = $this->container->get('fos_user.user_manager');
            $userObject = $userManager->findUserByUsername($user->getUsername());

            $em = $this->container->get('doctrine')->getManager();
            $mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')->find($sourceMediaID);
            if ($userObject == null) {
                throw new NotFoundHttpException ("This user does not exist");
            }
            return $this->render('IMDCTerpTubeBundle:MediaController:simultaneousPreviewAndRecord.html.twig', array(
                "mediaFile" => $mediaFile
            ));
        }

        // FIXME add the recording stuff here
        // throw new NotImplementedException("Not yet implemented");
        $user = $this->getUser();
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $userManager = $this->container->get('fos_user.user_manager');
        $userObject = $userManager->findUserByUsername($user->getUsername());
        if ($userObject == null) {
            throw new NotFoundHttpException ("This user does not exist");
        }
        $media = new Media ();
        $fileName = $media->getId();
        $media->setOwner($userObject);
        $media->setType(Media::TYPE_VIDEO);
        $currentTime = new \DateTime ('now');
        $media->setTitle("Recording-from-source" . $currentTime->format('Y-m-d-H:i'));

        $audioFile = $request->files->get("audio-blob", null);
        $videoFile = $request->files->get("video-blob", null);

        // FIXME If the max upload file size is reached it should return an error instead of crash
        // FIXME Need to sync the audio/videos
        $transcoder = $this->container->get('imdc_terptube.transcoder'); // ($this->get('logger'));
        // if ($audioFile ==null)
        // $mergedFile = $videoFile;
        // else
        $mergedFile = $transcoder->mergeAudioVideo($audioFile, $videoFile);
        $resourceFile = new ResourceFile ();
        $resourceFile->setMedia($media);
        $resourceFile->setWebmExtension("webm");
        $media->setIsReady(Media::READY_WEBM);
        $resourceFile->setFile($mergedFile);

        $media->setResource($resourceFile);

        $userObject->addResourceFile($media);

        $em = $this->container->get('doctrine')->getManager();

        $sourceMedia = $em->getRepository('IMDCTerpTubeBundle:Media')->find($sourceMediaID);
        $compoundMedia = new CompoundMedia ();
        $compoundMedia->setSource($sourceMedia);
        $compoundMedia->setTarget($media);
        $compoundMedia->setTargetStartTime($startTime);
        $compoundMedia->setType(0); // Simultaneous Recording

        $em->persist($resourceFile);
        $em->persist($media);
        $em->persist($compoundMedia);

        $em->flush();

        $resource = $media->getResource();
        $resourceFile = new File ($resource->getAbsolutePath());
        $fs = new Filesystem ();
        $fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
        $resource->setPath("webm");
        // $em->persist ( $resourceFile );
        $em->flush();

        $eventDispatcher = $this->container->get('event_dispatcher');
        $uploadedEvent = new UploadEvent ($media);
        $eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadedEvent);

        // $mediaObjectArray = JSEntities::getMediaObject($media);
        $compoundMediaObjectArray = JSEntities::getCompoundMediaObject($compoundMedia);

        $return = array(
            'responseCode' => 200,
            'feedback' => 'media added',
            'compoundMedia' => $compoundMediaObjectArray
        );
        $return = json_encode($return); // json encode the array
        return new Response ($return, 200, array(
            'Content-Type' => 'application/json'
        ));

        // return $this->render('IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig');
    }

    public function addAction(Request $request)
    {
        // if not ajax, throw an error
        if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
            throw new BadRequestHttpException ('Only Ajax POST calls accepted');
        }

        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $media = new Media ();
        $form = $this->createForm(new MediaType (), $media);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();

            $uploadedFile = $media->getResource()->getFile();
            $media->setTitle($uploadedFile->getClientOriginalName()); // TODO clean this filename
            $media->setOwner($user);

            // Get Mime Type
            // $finfo = finfo_open(FILEINFO_MIME_TYPE);
            // $mimeType = finfo_file($finfo, $uploadedFile->getRealPath());
            // finfo_close($finfo);

            $mimeType = $uploadedFile->getMimeType();
            $resourcePath = $uploadedFile->getRealPath();
            $fs = new Filesystem ();

            if ($mimeType == 'application/octet-stream') {
                $process = new Process ('file --mime-type ' . escapeshellarg($resourcePath));
                $process->run();

                // executes after the command finishes
                if (!$process->isSuccessful()) {
                    throw new \RuntimeException ($process->getErrorOutput());
                }

                $processOutput = $process->getOutput();
                $mimeType = substr($processOutput, strrpos($processOutput, ":") + 2);
            }

            $this->get('logger')->info('Mime-Type: ' . $mimeType);
            $type = Media::TYPE_OTHER;
            if (preg_match("/^video\/.*/", $mimeType))
                $type = Media::TYPE_VIDEO;
            else if (preg_match("/^audio\/.*/", $mimeType))
                $type = Media::TYPE_AUDIO;
            else if (preg_match("/^image\/.*/", $mimeType))
                $type = Media::TYPE_IMAGE;
            $this->get('logger')->info('Mime-Type: ' . $type);
            $media->setType($type);
            $this->get('logger')->info('Extension: ' . $uploadedFile->guessExtension());
            $this->get('logger')->info('Client Extension: ' . $uploadedFile->getClientOriginalExtension());
            $originalExtension = $uploadedFile->getClientOriginalExtension();
            // FFMPEG does not like the .bin extension, therefore rename it to an extension in the appropriate group type which FFMPEG can handle.
            // if ($uploadedFile->guessExtension () == "bin")
            // {
            // // if ($type == Media::TYPE_VIDEO)
            // // {
            // // $fs->rename ( $resourcePath, substr ( $resourcePath, 0, strrpos ( $resourcePath, "." ) ) . ".avi", true );
            // // $resource->setPath ( "avi" );
            // // }
            // // else if ($type == Media::TYPE_AUDIO)
            // // {
            // // $fs->rename ( $resourcePath, substr ( $resourcePath, 0, strrpos ( $resourcePath, "." ) ) . ".mp3", true );
            // // $resource->setPath ( "mp3" );
            // // }
            // // else if ($type == Media::TYPE_IMAGE)
            // // {
            // // $fs->rename ( $resourcePath, substr ( $resourcePath, 0, strrpos ( $resourcePath, "." ) ) . ".jpg", true );
            // // $resource->setPath ( "jpg" );
            // // }
            // $fs->rename ( $resourcePath, substr ( $resourcePath, 0, strrpos ( $resourcePath, "." ) ) . "." . $uploadedFile->getClientOriginalExtension (), true );
            // $resource->setPath ( $uploadedFile->getClientOriginalExtension () );
            // }

            $user->addResourceFile($media);

            $em->persist($media);
            $em->persist($user);
            $em->flush();

            $resourcePath = $media->getResource()->getAbsolutePath();
            if ($media->getResource()->getPath() == "bin") {
                $fs->rename($resourcePath, substr($resourcePath, 0, strrpos($resourcePath, ".")) . "." . $originalExtension, true);
                $media->getResource()->setPath($originalExtension);
            }
            $em->flush();

            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(UploadEvent::EVENT_UPLOAD, new UploadEvent ($media));

            $content = array(
                'wasUploaded' => true,
                'finished' => true, // TODO remove
                'media' => JSEntities::getMediaObject($media)
            );
        } else {
            $content = array(
                'wasUploaded' => false,
                'finished' => false
            ); // TODO remove
        }

        return new Response (json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function getInfoAction(Request $request)
    {
        // if not ajax, throw an exception
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException ('Only Ajax calls accepted');
        }

        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        /*
         * $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId); if (!$media) { throw new \Exception('forum not found'); }
         */

        $mediaIds = $request->get('mediaIds');
        $transformer = new MediaCollectionToIntArrayTransformer ($em);
        $mediaCollection = $transformer->reverseTransform($mediaIds);
        $ordered = Utils::orderMedia($mediaCollection, $mediaIds);

        $mediaJson = array();
        foreach ($ordered as $media) {
            $mediaJson [] = JSEntities::getMediaObject($media);
        }

        $content = array(
            'media' => $mediaJson
        );

        return new Response (json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}
