<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

use IMDC\TerpTubeBundle\Rest\RestResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestException
{
    const AREA = 'core';
    const CODE_PREFIX = 900;

    const INITIAL = 0;
    const NOT_FOUND = 1;
    const ACCESS_DENIED = 2;
    const BAD_REQUEST = 3;

    const MESSAGE_INITIAL = 'n/a';
    const MESSAGE_NOT_FOUND = 'not found';
    const MESSAGE_ACCESS_DENIED = 'access denied';
    const MESSAGE_INVALID_ARGUMENT = 'invalid argument';
    const MESSAGE_BAD_REQUEST = 'bad request';

    protected static $messageMap = array(
        self::INITIAL => self::MESSAGE_INITIAL,
        self::NOT_FOUND => self::MESSAGE_NOT_FOUND,
        self::ACCESS_DENIED => self::MESSAGE_ACCESS_DENIED,
        self::BAD_REQUEST => self::MESSAGE_BAD_REQUEST
    );

    protected static function getMessage($message = self::MESSAGE_INITIAL)
    {
        return static::AREA . '|' . $message;
    }

    protected static function getCode($code = self::INITIAL)
    {
        return intval(static::CODE_PREFIX . $code);
    }

    protected static function prepare($code, $message)
    {
        $message = strlen($message) > 0 ? $message : self::getMessage(static::$messageMap[$code]);
        $code = static::getCode($code);
        return new RestResponse($code, $message);
    }

    public static function Exception($message, $code = self::INITIAL)
    {
        $prepared = static::prepare($code, $message);
        throw new \Exception($prepared->getMessage(), $prepared->getCode());
    }

    public static function NotFound($message = '')
    {
        $prepared = static::prepare(self::NOT_FOUND, $message);
        throw new NotFoundHttpException($prepared->getMessage(), null, $prepared->getCode());
    }

    public static function AccessDenied($message = '')
    {
        $prepared = static::prepare(self::ACCESS_DENIED, $message);
        throw new AccessDeniedHttpException($prepared->getMessage(), null, $prepared->getCode());
    }

    public static function BadRequest($message = '')
    {
        $prepared = static::prepare(self::BAD_REQUEST, $message);
        throw new BadRequestHttpException($prepared->getMessage(), null, $prepared->getCode());
    }
}
