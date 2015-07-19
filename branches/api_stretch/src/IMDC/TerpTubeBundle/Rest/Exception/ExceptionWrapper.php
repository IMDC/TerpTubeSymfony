<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

class ExceptionWrapper
{
    protected $code;
    protected $area;
    protected $message;

    public function __construct($data)
    {
        $message = explode('|', $data['message']);

        if (count($message) == 2) {
            $this->code = $data['exception']->getCode();
            $this->area = $message[0];
            $this->message = $message[1];
        } else {
            $this->code = $data['status_code'];
            $this->area = 'unknown';
            $this->message = $message;
        }
    }
}
