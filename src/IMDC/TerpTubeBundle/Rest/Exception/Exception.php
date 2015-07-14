<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Exception
{
    const AREA = 'core';
    const CODE_PREFIX = 0;

    const INITIAL = 0;
    const NOT_FOUND = 10;
    const ACCESS_DENIED = 20;
    const INVALID_ARGUMENT = 30;

    const MESSAGE_INITIAL = '';
    const MESSAGE_NOT_FOUND = 'not found';
    const MESSAGE_ACCESS_DENIED = 'access denied';
    const MESSAGE_INVALID_ARGUMENT = 'invalid argument';

    private static $messageMap = array(
        self::INITIAL => self::MESSAGE_INITIAL,
        self::NOT_FOUND => self::MESSAGE_NOT_FOUND,
        self::ACCESS_DENIED => self::MESSAGE_ACCESS_DENIED,
        self::INVALID_ARGUMENT => self::MESSAGE_INVALID_ARGUMENT
    );

    protected static function getMessage($code = self::INITIAL, $message = '')
    {
        return static::AREA . '|' . (strlen($message) > 0 ? $message : static::$messageMap[$code]);
    }

    protected static function getCode($code = self::INITIAL)
    {
        return static::CODE_PREFIX . $code;
    }

    protected static function prepare($code, $message)
    {
        $message = strlen($message) > 0 ? $message : self::getMessage($code);
        $finalCode = static::getCode($code);
        return array($message, $finalCode);
    }

    public static function Exception($message, $code = self::INITIAL)
    {
        $prepared = static::prepare($code, $message);
        return new \Exception($prepared[0], $prepared[1]);
    }

    public static function NotFound($message = '')
    {
        $prepared = static::prepare(self::NOT_FOUND, $message);
        return new NotFoundHttpException($prepared[0], null, $prepared[1]);
    }

    public static function AccessDenied($message = '')
    {
        $prepared = static::prepare(self::ACCESS_DENIED, $message);
        return new AccessDeniedHttpException($prepared[0], null, $prepared[1]);
    }

    public static function InvalidArgument($message = '')
    {
        $prepared = static::prepare(self::INVALID_ARGUMENT, $message);
        return new \InvalidArgumentException($prepared);
    }
}
