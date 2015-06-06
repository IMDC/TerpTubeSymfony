<?php

namespace IMDC\TerpTubeBundle\Consumer;

class ConsumerOptions implements ConsumerOptionsInterface
{
    /**
     * @var integer
     */
    public $mediaId;

    public function pack()
    {
        return serialize($this);
    }

    public static function unpack($serialized)
    {
        return unserialize($serialized);
    }
}
