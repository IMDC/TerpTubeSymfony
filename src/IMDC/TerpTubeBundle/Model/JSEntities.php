<?php
namespace IMDC\TerpTubeBundle\Model;
use IMDC\TerpTubeBundle\Entity\CompoundMedia;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Post;

/**
 * @deprecated
 */
class JSEntities
{

    public static function getMediaObject (Media $media)
    {
        return array(
                'id' => $media->getId(),
                'type' => $media->getType(),
                'title' => $media->getTitle(),
                'isReady' => $media->getIsReady(),
                'metaData' => JSEntities::getMetaDataObject($media->getMetaData()),
                'owner' => $media->getOwner(),
                'resource' => JSEntities::getResourceObject($media->getResource()),
                'thumbnail' => $media->getThumbnailPath()
        );
    }

    public static function getCompoundMediaObject (CompoundMedia $media)
    {
        return array(
                'id' => $media->getId(),
                'type' => $media->getType(),
                'sourceMedia' => JSEntities::getMediaObject($media->getSource()),
                'targetMedia' => JSEntities::getMediaObject($media->getTarget()),
                'targetStartTime' => $media->getTargetStartTime()
        );
    }

    public static function getMetaDataObject (MetaData $metaData)
    {
        return array(
                'timeUploaded' => $metaData->getTimeUploaded(),
                'duration' => $metaData->getDuration(),
                'width' => $metaData->getWidth(),
                'height' => $metaData->getHeight(),
                'size' => $metaData->getSize(),
                'id' => $metaData->getId()
        );
    }

    public static function getResourceObject (ResourceFile $resource)
    {
        return array(
                'pathMPEG' => $resource->getWebPath(),
                'pathWebm' => $resource->getWebPathWebm()
        );
    }

    public static function getPostObject (Post $post)
    {
        return array(
                'id' => $post->getId(),
                'startTime' => $post->getStartTime(),
                'endTime' => $post->getEndTime()
        );
    }
}
