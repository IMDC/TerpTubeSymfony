<?php
namespace IMDC\TerpTubeBundle\Controller;
use Doctrine\Common\Collections\Criteria;
use FFMpeg\FFProbe;
use IMDC\TerpTubeBundle\Entity\Interpretation;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;

class MyFilesGatewayController extends Controller
{

    const FEEDBACK_MESSAGE_NOT_OWNER = "Not the rightful owner";

    const FEEDBACK_MESSAGE_NOT_EXIST_MEDIA = "Media does not exist";

    const FEEDBACK_MESSAGE_NOT_EXIST_USER = "User does not exist";

    const FEEDBACK_MESSAGE_MEDIA_IN_USE = "Media in use";

    const FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS = "Successfully removed media!";
    
    const FEEDBACK_MESSAGE_MEDIA_UPLOAD_INVALID_FORM = "Invalid Media!";

    public function listAction (Request $request)
    {
        if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $criteria = Criteria::create();
        $type = $request->query->get('type', false);
        $style = $request->query->get('style', 'grid');
        
        if ($type !== false)
        {
            $criteria->andWhere(Criteria::expr()->eq('type', $type));
        }
        
        $media = $this->getUser()
            ->getResourceFiles()
            ->matching($criteria);
        
        $paginator = $this->get('knp_paginator');
        $media = $paginator->paginate($media, $request->query->get('page', 1), /*page number*/
            ! $request->isXmlHttpRequest() ? 24 : ($style == 'list' ? 12 : 8) /*limit per page*/
        );
        
        $parameters = array(
                'media' => $media,
                'style' => $style
        );
        
        if (! $request->isXmlHttpRequest())
        {
            $parameters['uploadForm'] = $this->createForm(new MediaType())->createView();
        }
        
        $response = $this->render(
                'IMDCTerpTubeBundle:MyFiles:' . ($request->isXmlHttpRequest() ? 'ajax.' : '') . 'list.html.twig', 
                $parameters);
        
        if ($request->isXmlHttpRequest())
        {
            $content = array(
                    'page' => $response->getContent(),
                    'finished' => false
            );
            
            $response = new Response(json_encode($content), 200, 
                    array(
                            'Content-Type' => 'application/json'
                    ));
        }
        
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
    public function trimMediaAction (Request $request, $mediaId, $startTime, $endTime) // TODO move to MediaController
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
            $return = array(
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        }
        else 
            if ($media->getOwner() != $user)
            {
                $return = array(
                        'responseCode' => 400,
                        'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
                );
            }
            else
            {
                $serializer = $this->get('jms_serializer');
                // FIXME if video is being transcoded, need to queue the operation to execute once it completes
                // FIXME check if start/end times are proper values
                $resourceFile = $media->getResource();
                $webmFile = $resourceFile->getAbsolutePathWebm();
                $mp4File = $resourceFile->getAbsolutePath();
                $ffprobe = FFProbe::create();
                $metaData = $media->getMetaData();
                $transcoder = $this->container->get('imdc_terptube.transcoder');
                // FIXME Throws exception at rename when trying to move the mp4 file.
                if ($media->getIsReady() == Media::READY_YES)
                {
                    $resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
                    $resultMp4 = $transcoder->trimVideo($mp4File, $startTime, $endTime);
                    $finalFile = new File($webmFile);
                    $videoDuration = 0;
                    if ($ffprobe->format($finalFile->getRealPath())
                        ->has('duration'))
                        $videoDuration = $ffprobe->format($finalFile->getRealPath())
                            ->get('duration');
                    ;
                    $fileSize = filesize($finalFile->getRealPath());
                    $metaData->setDuration($videoDuration);
                    $metaData->setSize($fileSize);
                    $em->flush();
                    if ($resultWebM && $resultMp4)
                    {
                        $return = array(
                                'responseCode' => 200,
                                'feedback' => 'Successfully trimmed media!',
                                'media' => json_decode($serializer->serialize($media, 'json'), true)
                        );
                    }
                    else
                    {
                        $return = array(
                                'responseCode' => 400,
                                'feedback' => 'Trimming media failed!.'
                        );
                    }
                }
                else 
                    if ($media->getIsReady() == Media::READY_WEBM)
                    {
                        // FIXME this will encode a second time since the video was already queued for transcoding
                        // FIXME need to find out how to dequeue an item from the RabbitMQ queue
                        $resultWebM = $transcoder->trimVideo($webmFile, $startTime, $endTime);
                        $pendingOperations = $media->getPendingOperations();
                        if ($pendingOperations == null)
                            $pendingOperations = array();
                        array_push($pendingOperations, "trim,mp4," . $startTime . "," . $endTime);
                        $media->setPendingOperations($pendingOperations);
                        $finalFile = new File($webmFile);
                        $videoDuration = 0;
                        if ($ffprobe->format($finalFile->getRealPath())
                            ->has('duration'))
                            $videoDuration = $ffprobe->format($finalFile->getRealPath())
                                ->get('duration');
                        ;
                        $fileSize = filesize($finalFile->getRealPath());
                        $metaData->setDuration($videoDuration);
                        $metaData->setSize($fileSize);
                        $em->flush();
                        // $eventDispatcher = $this->container->get ( 'event_dispatcher' );
                        // $uploadedEvent = new UploadEvent ( $media );
                        // $eventDispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, $uploadedEvent );
                        if ($resultWebM)
                        {
                            $return = array(
                                    'responseCode' => 200,
                                    'feedback' => 'Successfully trimmed media!',
                                    'media' => json_decode($serializer->serialize($media, 'json'), true)
                            );
                        }
                        else
                        {
                            $return = array(
                                    'responseCode' => 400,
                                    'feedback' => 'Trimming media failed!.'
                            );
                        }
                    }
                    else
                    {
                        
                        $return = array(
                                'responseCode' => 400,
                                'feedback' => 'This should not happen!'
                        );
                    }
            }
        $return = json_encode($return); // json encode the array
        return new Response($return, 200, array(
                'Content-Type' => 'application/json'
        ));
    }

    public function updateMediaAction (Request $request, $mediaId) // TODO move to MediaController
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
            $return = array(
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        }
        else 
            if ($mediaToUpdate->getOwner() != $user)
            {
                $return = array(
                        'responseCode' => 400,
                        'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
                );
            }
            else
            {
                $media = json_decode($request->get('media'), true);
                if ($mediaToUpdate !== null && $media != null && $media['title'] !== null)
                {
                    $mediaToUpdate->setTitle($media['title']);
                    $em->flush();
                    $return = array(
                            'responseCode' => 200,
                            'feedback' => 'Successfully updated media!'
                    );
                }
                else
                {
                    $return = array(
                            'responseCode' => 400,
                            'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
                    );
                }
            }
        $return = json_encode($return); // json encode the array
        return new Response($return, 200, array(
                'Content-Type' => 'application/json'
        ));
    }

    /**
     * An Ajax function that deletes a media with a specific media ID
     *
     * @param Request $request            
     * @param unknown_type $mediaId            
     */
    public function deleteMediaAction (Request $request, $mediaId) // TODO move to MediaController
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
        // FIXME need to check if the video is used as a post somewhere and ask the user to confirm before deleting
        if ($media !== null)
        {
            if ($media->getOwner() != $user)
            {
                $return = array(
                        'responseCode' => 400,
                        'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_OWNER
                );
            }
            else
            {
                $needsConfirmation = false;
                
                // Find all places where the media can be used.
                // Also the interpretations
                // TODO this should be moved to its own method somewhere.
                $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getForumsForMedia($media);
                $messages = $em->getRepository('IMDCTerpTubeBundle:Message')->getMessagesForMedia($media);
                $posts = $em->getRepository('IMDCTerpTubeBundle:Post')->getPostsForMedia($media);
                $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getThreadsForMedia($media);
                
                $mediaInUse = array();
                if (count($forums) > 0)
                {
                    $needsConfirmation = true;
                    $mediaInUse[] = 'forum';
                }
                if (count($messages) > 0)
                {
                    $needsConfirmation = true;
                    $mediaInUse[] = 'message';
                }
                if (count($posts) > 0)
                {
                    $needsConfirmation = true;
                    $mediaInUse[] = 'post';
                }
                if (count($threads) > 0)
                {
                    $needsConfirmation = true;
                    $mediaInUse[] = 'thread';
                }
                if ($user->getProfile()->getAvatar() == $media)
                {
                    $needsConfirmation = true;
                    $mediaInUse[] = 'avatar';
                }
                
                if ($needsConfirmation)
                {
                    $confirm = $request->request->get('confirm');
                    $this->get('logger')->info("confirm: " . $confirm);
                    if ($confirm == 'true')
                    {
                        // User has confirmed, remove the media from all the places and then remove it
                        foreach ($forums as $forum)
                        {
                            $forum->removeTitleMedia($media);
                        }
                        foreach ($messages as $message)
                        {
                            $message->removeAttachedMedia($media);
                        }
                        foreach ($posts as $post)
                        {
                            $post->removeAttachedFile($media);
                        }
                        foreach ($threads as $thread)
                        {
                            $thread->removeMediaIncluded($media);
                        }
                        if ($user->getProfile()->getAvatar() == $media)
                        {
                            $user->getProfile()->setAvatar(null);
                        }
                        $em->remove($media);
                        $em->flush();
                        
                        $return = array(
                                'responseCode' => 200,
                                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
                        );
                    }
                    else
                    {
                        // User has not confirmed, send a confirmation message
                        $return = array(
                                'responseCode' => 400,
                                'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_IN_USE,
                                'mediaInUse' => $mediaInUse
                        );
                    }
                }
                else
                {
                    $em->remove($media);
                    $em->flush();
                    $return = array(
                            'responseCode' => 200,
                            'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
                    );
                }
            }
        }
        else
        {
            $return = array(
                    'responseCode' => 400,
                    'feedback' => MyFilesGatewayController::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            );
        }
        $return = json_encode($return); // json encode the array
        return new Response($return, 200, array(
                'Content-Type' => 'application/json'
        ));
    }

    public function addRecordingAction (Request $request)
    {
        // post requests only
        if (! $request->isMethod('POST'))
        {
            throw new BadRequestHttpException('POST requests only');
        }
        
        // check if user is logged in
        if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        $currentTime = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();
        
        $isFirefox = filter_var($request->request->get('isFirefox'), FILTER_VALIDATE_BOOLEAN);
        $video = $request->files->get('video-blob', null);
        $audio = $request->files->get('audio-blob', null);
        if (($isFirefox && empty($audio)) || (! $isFirefox && (empty($video) || empty($audio))))
        {
            throw new \Exception('no media data found in request');
        }
        
        $transcoder = $this->container->get('imdc_terptube.transcoder');
        
        $isInterpretation = filter_var($request->request->get('isInterpretation'), FILTER_VALIDATE_BOOLEAN);
        $sourceStartTime = floatval($request->request->get('sourceStartTime', 0));
        $sourceId = $request->request->get('sourceId', null);
        $sourceMedia = null;
        if ($isInterpretation)
        {
            $sourceMedia = $em->getRepository('IMDCTerpTubeBundle:Media')->find($sourceId);
            if (! $sourceMedia)
            {
                throw new \Exception('source media not found');
            }
        }
        
        $mergedFile = $isFirefox ? $transcoder->remuxWebM($audio) : $transcoder->mergeAudioVideo($audio, $video);
        $mergedFile = $transcoder->removeFirstFrame($mergedFile);
        
        $resourceFile = new ResourceFile();
        $resourceFile->setFile($mergedFile);
        $resourceFile->setWebmExtension('webm');
        
        if ($isInterpretation)
        {
            $media = new Interpretation();
            $media->setSourceStartTime($sourceStartTime);
            $media->setSource($sourceMedia);
        }
        else
        {
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
        
        // FIXME: transcoder seems to do this already. no need to rename and persist
        // Need to rename to webm since in some cases the recording is done as a .bin file
        $resource = $media->getResource();
        $resourceFile = new File($resource->getAbsolutePath());
        $targetFile = $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm';
        if (! file_exists($targetFile))
        {
            $fs = new Filesystem();
            $fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
        }
        $resource->setPath("webm");
        // $em->persist ( $resourceFile );
        $em->flush();
        
        $eventDispatcher = $this->container->get('event_dispatcher');
        $uploadEvent = new UploadEvent($media);
        $eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadEvent);
        
        $serializer = $this->get('jms_serializer');
        $content = array(
                'responseCode' => 200,
                'feedback' => 'media added',
                'media' => json_decode($serializer->serialize($media, 'json'), true)
        );
        
        return new Response(json_encode($content), 200, 
                array(
                        'Content-Type' => 'application/json'
                ));
    }

    public function addAction (Request $request)
    {
        // if not ajax, throw an error
        if (! $request->isXmlHttpRequest() || ! $request->isMethod('POST'))
        {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }
        
        // check if the user is logged in
        if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $media = new Media();
        $form = $this->createForm(new MediaType(), $media);
        $form->handleRequest($request);
        
        if ($form->isValid())
        {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            
            $uploadedFile = $media->getResource()->getFile();
            $media->setTitle($uploadedFile->getClientOriginalName()); // TODO clean this filename
            $media->setOwner($user);
            
            // Get Mime Type
            // $finfo = finfo_open(FILEINFO_MIME_TYPE);
            // $mimeType = finfo_file($finfo, $uploadedFile->getRealPath());
            // finfo_close($finfo);
            
            $resourcePath = $uploadedFile->getRealPath();
            $fs = new Filesystem();
            
            $type = Utils::getUploadedFileType($uploadedFile);
            $isValid = true;
            $transcoder = $this->container->get('imdc_terptube.transcoder');
            if ($type == Media::TYPE_AUDIO)
            {
                $isValid = $transcoder->checkAudioFile($uploadedFile);
            }
            else 
                if ($type == Media::TYPE_VIDEO)
                {
                    $isValid = $transcoder->checkVideoFile($uploadedFile);
                }
            if (! $isValid)
            {
                // Wrong audio/video type. return error
                $content = array(
                        'wasUploaded' => false,
                        'finished' => false,
                        'error' => Transcoder::INVALID_AUDIO_VIDEO_ERROR
                );
                // throw new \Exception(Transcoder::INVALID_AUDIO_VIDEO_ERROR);
            }
            else
            {
                $media->setType($type);
                $this->get('logger')->info('Extension: ' . $uploadedFile->guessExtension());
                $this->get('logger')->info('Client Extension: ' . $uploadedFile->getClientOriginalExtension());
                $originalExtension = $uploadedFile->getClientOriginalExtension();
                
                $user->addResourceFile($media);
                
                $em->persist($media);
                $em->persist($user);
                $em->flush();
                
                $resourcePath = $media->getResource()->getAbsolutePath();
                if ($media->getResource()->getPath() == "bin")
                {
                    $fs->rename($resourcePath, 
                            substr($resourcePath, 0, strrpos($resourcePath, ".")) . "." . $originalExtension, true);
                    $media->getResource()->setPath($originalExtension);
                }
                $em->flush();
                
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(UploadEvent::EVENT_UPLOAD, new UploadEvent($media));
                
                $serializer = $this->get('jms_serializer');
                $content = array(
                        'wasUploaded' => true,
                        'finished' => true, // TODO remove
                        'media' => json_decode($serializer->serialize($media, 'json'), true)
                );
            }
        }
        else
        {
            $content = array(
                    'wasUploaded' => false,
                    'finished' => false,
                    'error' => MyFilesGatewayController::FEEDBACK_MESSAGE_MEDIA_UPLOAD_INVALID_FORM
            ); // TODO remove
        }
        
        return new Response(json_encode($content), 200, 
                array(
                        'Content-Type' => 'application/json'
                ));
    }

    public function getInfoAction (Request $request) // TODO move to MediaController
    {
        // if not ajax, throw an exception
        if (! $request->isXmlHttpRequest())
        {
            throw new BadRequestHttpException('Only Ajax calls accepted');
        }
        
        // check if the user is logged in
        if (! $this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        /*
         * $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId); if (!$media) { throw new
         * \Exception('forum not found'); }
         */
        
        $mediaIds = $request->get('mediaIds');
        $transformer = new MediaCollectionToIntArrayTransformer($em);
        $mediaCollection = $transformer->reverseTransform($mediaIds);
        $ordered = Utils::orderMedia($mediaCollection, $mediaIds);
        
        $serializer = $this->get('jms_serializer');
        $content = array(
                'media' => json_decode($serializer->serialize($ordered, 'json'), true)
        );
        
        return new Response(json_encode($content), 200, 
                array(
                        'Content-Type' => 'application/json'
                ));
    }
}