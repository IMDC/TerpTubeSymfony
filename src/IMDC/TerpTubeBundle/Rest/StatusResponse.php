<?php

namespace IMDC\TerpTubeBundle\Rest;

class StatusResponse extends RestResponse
{
    const OK = 200;
    const MESSAGE_OK = 'OK';

    public function __construct($code = self::OK, $message = self::MESSAGE_OK)
    {
        parent::__construct($code, $message);
    }
}
