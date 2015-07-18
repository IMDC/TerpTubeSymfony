<?php

namespace IMDC\TerpTubeBundle\Rest;

class PostResponse extends RestResponse
{
    protected $post;
    protected $form;

    public function __construct($post, $code = null, $message = null)
    {
        parent::__construct($code, $message);

        $this->post = $post;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }
}
