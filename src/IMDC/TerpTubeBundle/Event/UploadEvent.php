<?php

namespace IMDC\TerpTubeBundle\Event;

use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\EventDispatcher\Event;

class UploadEvent extends Event
{
    const EVENT_UPLOAD = "imdc_terptube.event.uploadEvent";

    /**
     * @var Media
     */
    protected $media;

    /**
     * @var string
     */
    protected $tmpVideoPath;

    /**
     * @var string
     */
    protected $tmpAudioPath;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return string
     */
    public function getTmpVideoPath()
    {
        return $this->tmpVideoPath;
    }

    /**
     * @param string $tmpVideoPath
     */
    public function setTmpVideoPath($tmpVideoPath)
    {
        $this->tmpVideoPath = $tmpVideoPath;
    }

    /**
     * @return string
     */
    public function getTmpAudioPath()
    {
        return $this->tmpAudioPath;
    }

    /**
     * @param string $tmpAudioPath
     */
    public function setTmpAudioPath($tmpAudioPath)
    {
        $this->tmpAudioPath = $tmpAudioPath;
    }
}
