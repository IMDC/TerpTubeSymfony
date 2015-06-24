<?php

namespace IMDC\TerpTubeBundle\Consumer\Options;

abstract class AbstractConsumerOptions implements ConsumerOptionsInterface
{
    public function pack()
    {
        return serialize($this);
    }

    public static function unpack($serialized)
    {
        return unserialize($serialized);
    }
}
