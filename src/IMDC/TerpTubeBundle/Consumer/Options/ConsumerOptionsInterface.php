<?php

namespace IMDC\TerpTubeBundle\Consumer\Options;

interface ConsumerOptionsInterface
{
    public function pack();

    public static function unpack($serialized);
}
