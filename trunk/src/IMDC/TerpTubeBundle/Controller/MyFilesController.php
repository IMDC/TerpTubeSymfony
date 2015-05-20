<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use FFMpeg\FFProbe;
use IMDC\TerpTubeBundle\Entity\Interpretation;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyFilesController extends Controller
{
    const FEEDBACK_MESSAGE_MEDIA_UPLOAD_INVALID_FORM = "Invalid Media!";

    public function listAction(Request $request)
    {
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $criteria = Criteria::create()->orderBy(array("id" => Criteria::DESC));
        $type = $request->query->get('type', false);
        $style = $request->query->get('style', 'grid');

        if ($type !== false) {
            $criteria->andWhere(Criteria::expr()->eq('type', $type));
        }

        $media = $this->getUser()
            ->getResourceFiles()
            ->matching($criteria);

        $paginator = $this->get('knp_paginator');
        $media = $paginator->paginate(
            $media, $request->query->get('page', 1), /*page number*/
            !$request->isXmlHttpRequest() ? 24 : ($style == 'list' ? 12 : 8) /*limit per page*/
        );

        $parameters = array(
            'media' => $media,
            'style' => $style
        );

        if (!$request->isXmlHttpRequest()) {
            $parameters['form'] = $this->createForm('media_chooser', null, array(
                'allow_file_select' => false,
                'label' => false
            ))->createView();
        }

        $response = $this->render(
            'IMDCTerpTubeBundle:MyFiles:' . ($request->isXmlHttpRequest() ? 'ajax.' : '') . 'list.html.twig',
            $parameters);

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'page' => $response->getContent()
            );

            $response = new Response(json_encode($content), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        return $response;
    }

    public function addRecordingAction(Request $request)
    {
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

        $transcoder = $this->container->get('imdc_terptube.transcoder');

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

        $mergedFile = $isFirefox ? $transcoder->remuxWebM($audio) : $transcoder->mergeAudioVideo($audio, $video);
        $mergedFile = $transcoder->removeFirstFrame($mergedFile);

        $resourceFile = new ResourceFile();
        $resourceFile->setFile($mergedFile);
        //$resourceFile->setWebmExtension('webm');

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

        $user->addResourceFile($media);

        $em->persist($resourceFile);
        $em->persist($media);
        $em->persist($user);
        $em->flush();

        // FIXME: transcoder seems to do this already. no need to rename and persist
        // Need to rename to webm since in some cases the recording is done as a .bin file
        /*$resource = $media->getResource();
        $resourceFile = new File($resource->getAbsolutePath());
        $targetFile = $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm';
        if (!file_exists($targetFile)) {
            $fs = new Filesystem();
            $fs->rename($resourceFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
        }
        $resource->setPath("webm");
        // $em->persist ( $resourceFile );
        $em->flush();*/

        $eventDispatcher = $this->container->get('event_dispatcher');
        $uploadEvent = new UploadEvent($media);
        $eventDispatcher->dispatch(UploadEvent::EVENT_UPLOAD, $uploadEvent);

        $serializer = $this->get('jms_serializer');
        $content = array(
            'responseCode' => 200,
            'feedback' => 'media added',
            'media' => json_decode($serializer->serialize($media, 'json'), true)
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function addAction(Request $request)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $media = new Media();
        $form = $this->createForm(new MediaType(), $media);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();

            $uploadedFile = $media->getResource()->getFile();
            $mediaType = Utils::getUploadedFileType($uploadedFile->getMimeType());

            $this->get('logger')->info('Extension: ' . $uploadedFile->guessExtension());
            $this->get('logger')->info('Client Extension: ' . $uploadedFile->getClientOriginalExtension());

            $media->setType($mediaType);

            /** @var $transcoder Transcoder */
            $transcoder = $this->get('imdc_terptube.transcoder');

            // check if ffmpeg supports an otherwise misidentified video/audio file
            $ffprobe = $transcoder->getFFprobe();

            try {
                /** @var $streams FFProbe\DataMapping\StreamCollection */
                $streams = $ffprobe->streams($uploadedFile->getRealPath());
                $media->setType(
                    $streams->videos()->count() > 0
                        ? Media::TYPE_VIDEO
                        : ($streams->audios()->count() > 0
                            ? Media::TYPE_AUDIO
                            : null));
            } catch (\Exception $e) {
                $media->setType(null);
            }

            // check if unix file cmd and ffmpeg agreed
            if ($media->getType() == null && ($mediaType == Media::TYPE_VIDEO || $mediaType == Media::TYPE_AUDIO)) {
                // Wrong audio/video type. return error
                //TODO generic message factory
                return new Response(json_encode(array(
                    'wasUploaded' => false,
                    'error' => Transcoder::INVALID_AUDIO_VIDEO_ERROR
                )), 200, array(
                    'Content-Type' => 'application/json'
                ));
                // throw new \Exception(Transcoder::INVALID_AUDIO_VIDEO_ERROR);
            }

            $media->setTitle($uploadedFile->getClientOriginalName()); //TODO clean this filename
            $media->setOwner($user);

            // Get Mime Type
            // $finfo = finfo_open(FILEINFO_MIME_TYPE);
            // $mimeType = finfo_file($finfo, $uploadedFile->getRealPath());
            // finfo_close($finfo);

            /*$resourcePath = $uploadedFile->getRealPath();
            $fs = new Filesystem();

            $type = Utils::getUploadedFileType($uploadedFile);
            $isValid = true;
            if ($type == Media::TYPE_AUDIO) {
                $isValid = $transcoder->checkAudioFile($uploadedFile);
            } else
                if ($type == Media::TYPE_VIDEO) {
                    $isValid = $transcoder->checkVideoFile($uploadedFile);
                }
            if (!$isValid) {
                // Wrong audio/video type. return error
                $content = array(
                    'wasUploaded' => false,
                    'error' => Transcoder::INVALID_AUDIO_VIDEO_ERROR
                );
                // throw new \Exception(Transcoder::INVALID_AUDIO_VIDEO_ERROR);
            } else {
                $media->setType($type);
                $this->get('logger')->info('Extension: ' . $uploadedFile->guessExtension());
                $this->get('logger')->info('Client Extension: ' . $uploadedFile->getClientOriginalExtension());
                $originalExtension = $uploadedFile->getClientOriginalExtension();*/

                $user->addResourceFile($media);

                $em->persist($media);
                $em->persist($user);
                $em->flush();

                /*$resourcePath = $media->getResource()->getAbsolutePath();
                if ($media->getResource()->getPath() == "bin") {
                    $fs->rename($resourcePath,
                        substr($resourcePath, 0, strrpos($resourcePath, ".")) . "." . $originalExtension, true);
                    $media->getResource()->setPath($originalExtension);
                }
                $em->flush();*/

                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(UploadEvent::EVENT_UPLOAD, new UploadEvent($media));

                $serializer = $this->get('jms_serializer');
                $content = array(
                    'wasUploaded' => true,
                    'media' => json_decode($serializer->serialize($media, 'json'), true)
                );
            //}
        } else {
            $content = array(
                'wasUploaded' => false,
                'error' => self::FEEDBACK_MESSAGE_MEDIA_UPLOAD_INVALID_FORM
            );
        }

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}
