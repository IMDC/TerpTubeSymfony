<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

use FOS\RestBundle\View\View;
use IMDC\TerpTubeBundle\Rest\MediaResponse;

class MediaException extends RestException
{
    const AREA = 'media';
    const CODE_PREFIX = 920;

    //TODO revise
    const MESSAGE_NOT_EXIST_MEDIA = 'Media does not exist';
    const MESSAGE_NOT_OWNER = 'Not the rightful owner';
    const MESSAGE_IN_USE = 'Media in use';

    public static function NotFound()
    {
        parent::NotFound(self::MESSAGE_NOT_EXIST_MEDIA);
    }

    public static function AccessDenied()
    {
        parent::AccessDenied(self::MESSAGE_NOT_OWNER);
    }

    public static function InUse($media)
    {
        $prepared = static::prepare(self::INITIAL, self::MESSAGE_IN_USE);

        $resp = new MediaResponse(null, $prepared->getCode(), $prepared->getMessage());
        $resp->setInUse($media);

        return View::create($resp, 500);
    }
}
