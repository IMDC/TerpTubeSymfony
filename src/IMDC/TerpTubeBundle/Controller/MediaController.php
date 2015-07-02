<?php

namespace IMDC\TerpTubeBundle\Controller;

use FFMpeg\FFProbe;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use IMDC\TerpTubeBundle\Consumer\TrimConsumerOptions;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Utils\Utils;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends FOSRestController implements ClassResourceInterface
{
    const FEEDBACK_MESSAGE_NOT_EXIST_MEDIA = 'Media does not exist';
    const FEEDBACK_MESSAGE_NOT_OWNER = 'Not the rightful owner';
    const FEEDBACK_MESSAGE_MEDIA_IN_USE = 'Media in use';
    const FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS = 'Successfully removed media!';

    /**
     * @Rest\View()
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cgetAction(Request $request)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $ids = array_filter(explode(',', $request->get('id', '')));

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('IMDCTerpTubeBundle:Media')->createQueryBuilder('m');
        if (!empty($ids)) {
            $qb->where($qb->expr()->in('m.id', $ids));
        }
        $media = Utils::orderMedia($qb->getQuery()->getResult(), $ids);

        return $this->view(array('media' => $media), 200);
    }

    /**
     * @Rest\View()
     *
     * @param Request $request
     * @param $mediaId
     * @return \FOS\RestBundle\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function getAction(Request $request, $mediaId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 500); //TODO decide status code
        }

        return $this->view(array('media' => $media), 200);
    }

    /**
     * @Rest\Post()
     *
     * @param Request $request
     * @param $mediaId
     * @return \FOS\RestBundle\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, $mediaId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $mediaPayload = json_decode($request->get('media'), true);

        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media || !$mediaPayload || !array_key_exists('title', $mediaPayload)) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 500); //TODO decide status code
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 500); //TODO decide status code
        }

        $media->setTitle($mediaPayload['title']);

        $em->persist($media);
        $em->flush();

        return $this->view(array('media' => $media), 200);
    }

    /**
     * Deletes a media of the specific media id
     *
     * @Rest\View()
     *
     * @param Request $request
     * @param $mediaId
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, $mediaId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 500); //TODO decide status code
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 500); //TODO decide status code
        }

        //TODO revise everything below
        //TODO update media display order where media is removed

        $needsConfirmation = false;

        // Find all places where the media can be used.
        // Also the interpretations
        // TODO this should be moved to its own method somewhere.
        $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getForumsForMedia($media);
        $messages = $em->getRepository('IMDCTerpTubeBundle:Message')->getMessagesForMedia($media);
        $posts = $em->getRepository('IMDCTerpTubeBundle:Post')->getPostsForMedia($media);
        $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->getThreadsForMedia($media);
        //TODO move this
        $groups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->createQueryBuilder('g')
            ->leftJoin('g.media', 'm')
            ->where('m.id = :mediaId')
            ->setParameters(array(
                'mediaId' => $media->getId()
            ))->getQuery()->getResult();

        $mediaInUse = array();
        if (count($forums) > 0) {
            $needsConfirmation = true;
            $mediaInUse[] = 'forum';
        }
        if (count($messages) > 0) {
            $needsConfirmation = true;
            $mediaInUse[] = 'message';
        }
        if (count($posts) > 0) {
            $needsConfirmation = true;
            $mediaInUse[] = 'post';
        }
        if (count($threads) > 0) {
            $needsConfirmation = true;
            $mediaInUse[] = 'thread';
        }
        if (count($groups) > 0) {
            $needsConfirmation = true;
            $mediaInUse[] = 'group';
        }
        if ($user->getProfile()->getAvatar() == $media) {
            $needsConfirmation = true;
            $mediaInUse[] = 'avatar';
        }

        $confirm = filter_var($request->request->get('confirm', false), FILTER_VALIDATE_BOOLEAN);
        $this->get('logger')->info("confirm: " . $confirm);
        if ($needsConfirmation && !$confirm) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_MEDIA_IN_USE,
                'mediaInUse' => $mediaInUse
            )), 500); //TODO decide status code
        }

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
        foreach ($groups as $group) {
            $group->removeMedia($media);
        }
        if ($user->getProfile()->getAvatar() == $media) {
            $user->getProfile()->setAvatar(null);
        }

        $em->remove($media);
        $em->flush();

        return $this->view(array('status' => array(
            'code' => 0,
            'message' => self::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
        )), 200);
    }

    /**
     * Trims a media of a specific media id, to the specified start and end times
     *
     * @Rest\View()
     *
     * @param Request $request
     * @param $mediaId
     * @return \FOS\RestBundle\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function trimAction(Request $request, $mediaId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 500); //TODO decide status code
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 500); //TODO decide status code
        }

        $startTime = floatval($request->get('startTime', 0));
        $endTime = floatval($request->get('endTime', 0));
        if ($startTime == 0 && $endTime == 0) {
            //TODO api exception
            return $this->view(array('error' => array(
                'code' => 0,
                'message' => 'invalid argument'
            )), 500); //TODO decide status code
        }

        /** @var ResourceFile $resource */
        $resource = $media->getResources()->get(0);

        $trimOpts = new TrimConsumerOptions();
        $trimOpts->mediaId = $media->getId();
        $trimOpts->startTime = $startTime;
        $trimOpts->endTime = $endTime;
        $trimOpts->currentDuration = $resource->getMetaData()->getDuration();

        /** @var Producer $trimProducer */
        $trimProducer = $this->container->get('old_sound_rabbit_mq.trim_producer');
        $trimProducer->publish($trimOpts->pack());

        //TODO return OK only since the entity would not have changed in this method
        return $this->view(array('media' => $media), 200);
    }
}
