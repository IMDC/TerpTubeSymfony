<?php

namespace IMDC\TerpTubeBundle\Rest;

class MediaResponse extends RestResponse
{
    const MESSAGE_DELETE_SUCCESS = 'Successfully removed media!';

    protected $media;
    protected $inUse;

    public function __construct($media, $code = null, $message = null)
    {
        parent::__construct($code, $message);

        $this->media = $media;
    }

    /**
     * @return int
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param int $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return mixed
     */
    public function getInUse()
    {
        return $this->inUse;
    }

    /**
     * @param mixed $inUse
     */
    public function setInUse($inUse)
    {
        $this->inUse = $inUse;
    }
}
