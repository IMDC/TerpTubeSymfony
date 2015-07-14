<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

class MediaException extends Exception
{
    const AREA = 'media';
    const CODE_PREFIX = 920;

    //TODO revise
    const MESSAGE_NOT_EXIST_MEDIA = 'Media does not exist';
    const MESSAGE_NOT_OWNER = 'Not the rightful owner';
    const MESSAGE_IN_USE = 'Media in use';

    public static function NotFound()
    {
        return parent::NotFound(self::MESSAGE_NOT_EXIST_MEDIA);
    }

    public static function AccessDenied()
    {
        return parent::AccessDenied(self::MESSAGE_NOT_OWNER);
    }

    public static function InvalidArgument()
    {
        return parent::InvalidArgument(self::MESSAGE_NOT_OWNER);
    }
}
