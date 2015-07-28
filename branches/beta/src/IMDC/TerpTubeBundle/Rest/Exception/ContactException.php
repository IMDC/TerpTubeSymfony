<?php

namespace IMDC\TerpTubeBundle\Rest\Exception;

class ContactException extends RestException
{
    const AREA = 'contact';
    const CODE_PREFIX = 930;

    const MESSAGE_INVALID_LIST = 'invalid contact list';
}
