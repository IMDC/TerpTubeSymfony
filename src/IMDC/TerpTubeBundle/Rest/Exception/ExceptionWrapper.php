<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

class ExceptionWrapper
{
    protected $code;
    protected $area;
    protected $message;

    public function __construct($data)
    {
        $this->code = $data['exception']->getCode();

        $message = explode('|', $data['message']);
        $this->area = $message[0];
        $this->message = $message[1];
    }
}
