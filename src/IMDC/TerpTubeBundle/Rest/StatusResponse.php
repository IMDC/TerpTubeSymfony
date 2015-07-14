<?php

namespace IMDC\TerpTubeBundle\Rest;

class StatusResponse
{
    const MESSAGE_OK = 'OK';

    protected $code;
    protected $message;

    public function __construct($code, $message = self::MESSAGE_OK)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
