<?php

namespace IMDC\TerpTubeBundle\Consumer;

interface ConsumerOptionsInterface
{
    public function pack();

    public static function unpack($serialized);
}
