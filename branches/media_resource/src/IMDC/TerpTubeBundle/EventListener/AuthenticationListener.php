<?php

namespace IMDC\TerpTubeBundle\EventListener;

use IMDC\TerpTubeBundle\Component\Authentication\AuthenticationManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;

class AuthenticationListener
{
    /**
     * @var AuthenticationManager
     */
    protected $authenticationManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Router
     */
    protected $router;

    public function __construct(AuthenticationManager $authenticationManager, Translator $translator, Router $router)
    {
        $this->authenticationManager = $authenticationManager;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernel::MASTER_REQUEST)
            return;

        $request = $event->getRequest();

        if ($this->authenticationManager->resourceRequiresAuthentication($request) && !$this->authenticationManager->isAuthenticated()) {
            if ($request->isXmlHttpRequest()) {
                $response = new Response($this->translator->trans('security.login.not_logged_in', array(), 'IMDCTerpTubeBundle'), 403);
            } else {
                $response = new RedirectResponse($this->router->generate('fos_user_security_login'));
            }

            $event->setResponse($response);
        }
    }
}
