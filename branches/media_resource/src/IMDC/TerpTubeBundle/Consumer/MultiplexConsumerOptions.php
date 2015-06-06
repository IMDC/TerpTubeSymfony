<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Symfony\Component\HttpFoundation\File\File;

abstract class MultiplexOperation
{
    const MUX = 0;
    const REMUX = 1;
}

class MultiplexConsumerOptions extends ConsumerOptions
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

    /**
     * @var TranscodeConsumerOptions
     */
    public $transcodeOptions;

    public function pack()
    {
        $this->video = null;
        $this->audio = null;

        $temp = $this->transcodeOptions;
        $this->transcodeOptions = $this->transcodeOptions->pack();

        $serialized = serialize($this);

        $this->transcodeOptions = $temp;

        return $serialized;
    }

    public static function unpack($serialized)
    {
        $opts = unserialize($serialized);

        if (is_file($opts->videoPath))
            $opts->video = new File($opts->videoPath);
        if (is_file($opts->audioPath))
            $opts->audio = new File($opts->audioPath);

        $opts->transcodeOptions = TranscodeConsumerOptions::unpack($opts->transcodeOptions);

        return $opts;
    }
}
