<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Symfony\Component\HttpFoundation\File\File;

abstract class MultiplexOperation
{
    const MUX = 0;
    const REMUX = 1;
}

class MultiplexConsumerOptions extends AbstractMediaConsumerOptions
{
    /**
     * @var MultiplexOperation
     */
    public $operation;

    /**
     * @var string
     */
    public $videoPath;

    /**
     * @var string
     */
    public $audioPath;

    /**
     * @var File
     */
    public $video;

    /**
     * @var File
     */
    public $audio;

    public function pack()
    {
        $this->video = null;
        $this->audio = null;

        return serialize($this);
    }

    public static function unpack($serialized)
    {
        $opts = unserialize($serialized);

        if (is_file($opts->videoPath))
            $opts->video = new File($opts->videoPath);
        if (is_file($opts->audioPath))
            $opts->audio = new File($opts->audioPath);

        return $opts;
    }
}
