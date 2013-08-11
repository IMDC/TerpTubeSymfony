<?php

namespace IMDC\TerpTubeBundle\EventListener;
	
use IMDC\TerpTubeBundle\Entity\Message;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use IMDC\TerpTubeBundle\Entity\User;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class NewUserRegistrationListener implements EventSubscriberInterface
{
	private $logger;
	private $doctrine;
	
	public function __construct($logger, $doctrine)
	{
		$this->logger   = $logger;
		$this->doctrine = $doctrine;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
            FOSUserEvents::REGISTRATION_CONFIRMED => 'generateIntroductionEmail',
		);
	}
	
	public function generateIntroductionEmail(FilterUserResponseEvent $event)
    {
    	$this->logger->info('I am inside generateIntroductionEmail');
    	$em = $this->doctrine->getManager();
    	$user = $event->getUser();
    	    	
    	$message = new Message;
    	$message->addRecipient($user);
    	// todo: set the owner of this email to the admin of the website
    	// todo: create a user with id=0 that can be the admin of the website
    	$message->setOwner($user);
    	$message->setSubject('Welcome to TerpTube');
    	$message->setContent('Hi ' . $user->getProfile()->getFirstName() . 
    							"\n\nWelcome to Terptube, we are glad to have you!".
    							"\n\n - Your friends at TerpTube");
    	$user->addReceivedMessage($message);
    	$em->persist($message);
    	$em->persist($user);
    	$em->flush();
    	
    	return;
    }
}