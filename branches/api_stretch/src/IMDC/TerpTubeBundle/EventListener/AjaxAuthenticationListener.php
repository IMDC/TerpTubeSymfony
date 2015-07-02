<?php

namespace IMDC\TerpTubeBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use IMDC\TerpTubeBundle\Utils\AuthenticationManager;
use Symfony\Component\Translation\Translator;

class AjaxAuthenticationListener
{
	protected $authenticationManager;
	protected $translator;
	public function __construct(AuthenticationManager $authenticationManager, Translator $translator)
	{
		$this->authenticationManager = $authenticationManager;
		$this->translator = $translator;
	}
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest ();
		if ($request->isXmlHttpRequest ())
		{
			if (! $this->authenticationManager->isAuthenticated ( $request ))
				$event->setResponse ( new Response ( $this->translator->trans ( 'security.login.not_logged_in', array (), 'IMDCTerpTubeBundle' ), 403 ) );
			else
				return;
		}
	}
}


