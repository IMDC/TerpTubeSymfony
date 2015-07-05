<?php

namespace IMDC\TerpTubeBundle\Component\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\SecurityContext;

class AuthenticationManager
{
    /**
     * @var array
     */
    protected static $openRoutes = array(
        'fos_user_security_.*',
        'fos_user_registration_.*',
        'fos_user_resetting_.*',
        'fos_js_routing_.*',
        'imdc_index'
    );

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    public function __construct(Kernel $kernel, SecurityContext $securityContext)
    {
        $this->kernel = $kernel;
        $this->securityContext = $securityContext;
    }

    public function resourceRequiresAuthentication(Request $request)
    {
        $route = $request->get('_route');
        foreach (self::$openRoutes as $pattern) {
            if (preg_match('/' . $pattern . '/', $route))
                return false;
        }

        return true;
    }

    public function isAuthenticated()
    {
        // In the dev environment there will be no token for many resources
        if ($this->kernel->getEnvironment() != 'prod' && $this->securityContext->getToken() == null)
            return true;

        return $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}