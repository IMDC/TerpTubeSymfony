<?php

namespace IMDC\TerpTubeBundle\Controller;

use FFMpeg\FFProbe;
use IMDC\TerpTubeBundle\Consumer\TrimConsumerOptions;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Utils\Utils;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    const FEEDBACK_MESSAGE_NOT_EXIST_MEDIA = 'Media does not exist';
    const FEEDBACK_MESSAGE_NOT_OWNER = 'Not the rightful owner';
    const FEEDBACK_MESSAGE_MEDIA_IN_USE = 'Media in use';
    const FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS = 'Successfully removed media!';

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function listAction(Request $request) //TODO api?
    {
        $ids = array_filter(explode(',', $request->get('id', '')));

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('IMDCTerpTubeBundle:Media')->createQueryBuilder('m');
        if (!empty($ids)) {
            $qb->where($qb->expr()->in('m.id', $ids));
        }
        $collection = Utils::orderMedia($qb->getQuery()->getResult(), $ids);

        $serializer = $this->get('jms_serializer');
        $content = array(
            'media' => json_decode($serializer->serialize($collection, 'json'), true)
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param Request $request
     * @param $mediaId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $mediaId) //TODO api?
    {
        $mediaPayload = json_decode($request->get('media'), true);

        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media || !$mediaPayload || !array_key_exists('title', $mediaPayload)) {
            //throw new \Exception('media not found');
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //throw new AccessDeniedException();
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $media->setTitle($mediaPayload['title']);

        $em->flush();

        $serializer = $this->get('jms_serializer');
        $content = array(
            'responseCode' => 200,
            'feedback' => 'Successfully updated media!',
            'media' => json_decode($serializer->serialize($media, 'json'), true)
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * Deletes a media of the specific media id
     *
     * @param Request $request
     * @param $mediaId
     * @return Response
     */
    public function deleteAction(Request $request, $mediaId) //TODO api?
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            //throw new \Exception('media not found');
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //throw new AccessDeniedException();
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
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
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_MEDIA_IN_USE,
                'mediaInUse' => $mediaInUse
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
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

        $content = array(
            'responseCode' => 200,
            'feedback' => self::FEEDBACK_MESSAGE_MEDIA_DELETE_SUCCESS
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * Trims a media of a specific media id, to the specified start and end times
     *
     * @param Request $request
     * @param $mediaId
     * @return Response
     */
    public function trimAction(Request $request, $mediaId) //TODO api?
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $media Media */
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (!$media) {
            //throw new \Exception('media not found');
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_EXIST_MEDIA
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $user = $this->getUser();
        if ($media->getOwner() != $user) {
            //throw new AccessDeniedException();
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => self::FEEDBACK_MESSAGE_NOT_OWNER
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $startTime = floatval($request->get('startTime', 0));
        $endTime = floatval($request->get('endTime', 0));
        if ($startTime == 0 && $endTime == 0) {
            return new Response(json_encode(array(
                'responseCode' => 400,
                'feedback' => 'invalid argument'
            )), 200, array(
                'Content-Type' => 'application/json'
            ));
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

        $content = array(
            'responseCode' => 200,
            'feedback' => 'trim queued'
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}
