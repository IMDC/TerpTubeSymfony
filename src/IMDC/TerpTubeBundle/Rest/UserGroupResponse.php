<?php

namespace IMDC\TerpTubeBundle\Rest;

class UserGroupResponse extends RestResponse
{
    protected $redirectUrl;

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }
}
