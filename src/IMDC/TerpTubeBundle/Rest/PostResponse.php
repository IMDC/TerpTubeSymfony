<?php

namespace IMDC\TerpTubeBundle\Rest;

class PostResponse
{
    protected $post;

    public function __construct($post)
    {
        $this->post = $post;
    }
}
