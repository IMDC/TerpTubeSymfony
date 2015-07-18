<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

use FOS\RestBundle\View\View;
use IMDC\TerpTubeBundle\Rest\PostResponse;

class PostException extends RestException
{
    const AREA = 'post';
    const CODE_PREFIX = 910;

    public static function InvalidForm($form)
    {
        $prepared = static::prepare(self::BAD_REQUEST, 'invalid form');

        $resp = new PostResponse(null, $prepared->getCode(), $prepared->getMessage());
        $resp->setForm($form);
        return View::create($resp, 400);
    }
}
