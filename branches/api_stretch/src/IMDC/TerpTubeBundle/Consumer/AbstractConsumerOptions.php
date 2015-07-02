<?php

namespace IMDC\TerpTubeBundle\Consumer;

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
