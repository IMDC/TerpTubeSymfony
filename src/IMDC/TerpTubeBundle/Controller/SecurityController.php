<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as FOSUserBundleSecurityController;

class SecurityController extends FOSUserBundleSecurityController
{
    protected function renderLogin(array $data)
    {
        $request = $this->container->get('request');
        $route = $request->get('_route');

        return $route == 'fos_user_security_login'
            ? parent::renderLogin($data)
            : $this->container->get('templating')->renderResponse('IMDCTerpTubeBundle:Member:navbarLogin.html.twig', $data);
    }

}
