<?php

namespace IMDC\TerpTubeBundle\Rest;

class MediaResponse extends RestResponse
{
    const MESSAGE_DELETE_SUCCESS = 'Successfully removed media!';

    protected $media;

    public function __construct($media)
    {
        $this->media = $media;
    }
}
