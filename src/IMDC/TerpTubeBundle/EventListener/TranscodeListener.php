<?php

namespace IMDC\TerpTubeBundle\EventListener;
	
use AC\Transcoding\Event\MessageEvent;

use AC\Transcoding\Event\TranscodeEvent;

use AC\Transcoding\Event\TranscodeEvents;

use IMDC\TerpTubeBundle\Entity\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use IMDC\TerpTubeBundle\Entity\User;

/**
 * Listener for the transcoding events
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
		$this->logger->info('Transcoding Message: ' . $event->getMessage());
		 
		return;
	}
	
	public function printError(TranscodeEvent $event)
    {
    	$this->logger->critical('Transcoding Error: ---------');
    	$this->logger->critical($event->getException());
    	return;
    }
    
    public function fileConverted(TranscodeEvent $event)
    {
    	$this->logger->info('Transcoding Successfull from File '. $event->getInputPath() .' to: ' .$event->getOutputPath());
    	 
    	return;
    }
}