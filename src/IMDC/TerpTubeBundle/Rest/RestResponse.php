<?php

namespace IMDC\TerpTubeBundle\Rest;

class RestResponse
{
    const OK = 200;
    const MESSAGE_OK = 'OK';

    protected $code;
    protected $message;

    public function __construct($code = self::OK, $message = self::MESSAGE_OK)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
