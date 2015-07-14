<?php

namespace IMDC\TerpTubeBundle\Rest;

class MediaResponse
{
    const MESSAGE_DELETE_SUCCESS = 'Successfully removed media!';

    protected $media;

    public function __construct($media)
    {
        $this->media = $media;
    }
}
