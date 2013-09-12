<?php

namespace IMDC\TerpTubeBundle\EventListener;
	
use AC\Transcoding\Event\MessageEvent;

use AC\Transcoding\Event\TranscodeEvent;

use AC\Transcoding\Event\TranscodeEvents;

use IMDC\TerpTubeBundle\Entity\Message;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use IMDC\TerpTubeBundle\Entity\User;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Doctrine\UserManager;

/**
 * Listener for the 'completed' event of user registration.
 * Creates an introduction message so the user finds a message
 * in their inbox upon first login
 * 
 * @author paul
 *
 */
class TranscodeListener implements EventSubscriberInterface
{
	private $logger;
	private $doctrine;
	private $userManager;
	
	public function __construct($logger, $doctrine, $usermanager)
	{
		$this->logger   = $logger;
		$this->doctrine = $doctrine;
		$this->userManager = $usermanager;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
            TranscodeEvents::ERROR => 'printError',
			TranscodeEvents::MESSAGE => 'printMessage',
			TranscodeEvents::AFTER => 'fileConverted',
		);
	}
	
	public function printMessage(MessageEvent $event)
	{
		$this->logger->info('Transcode Message');
		$this->logger->info($event->getMessage());
		 
		return;
	}
	
	public function printError(TranscodeEvent $event)
    {
    	$this->logger->info('Transcode Error');
    	$this->logger->info($event->getException());
    	
    	return;
    }
    
    public function fileConverted(TranscodeEvent $event)
    {
    	$this->logger->info('File '. $event->getInputPath() .' converted to: ' .$event->getOutputPath());
    	 
    	return;
    }
}