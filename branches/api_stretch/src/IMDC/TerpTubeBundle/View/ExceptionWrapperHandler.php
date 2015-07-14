<?php

namespace IMDC\TerpTubeBundle\View;

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use IMDC\TerpTubeBundle\Rest\Exception\ExceptionWrapper;

class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return new ExceptionWrapper($data);
    }
}
