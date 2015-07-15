<?php

namespace IMDC\TerpTubeBundle\EventListener;

use IMDC\TerpTubeBundle\Entity\Message;
use FOS\UserBundle\FOSUserEvents;
use IMDC\TerpTubeBundle\Entity\UserProfile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use IMDC\TerpTubeBundle\Entity\User;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\FormEvent;

/**
 * Listener for the 'completed' event of user registration.
 * Creates an introduction message so the user finds a message
 * in their inbox upon first login
 * 
 * @author paul
 *
 */
class FOSUserListener implements EventSubscriberInterface
{
	private $logger;
	private $doctrine;
	private $userManager;
    private $router;
	
	public function __construct($logger, $doctrine, UrlGeneratorInterface $router, $usermanager)
	{
		$this->logger   = $logger;
		$this->doctrine = $doctrine;
        $this->router = $router;
		$this->userManager = $usermanager;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'generateIntroductionEmail',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess'
		);
	}

	/**
	 * @param GetResponseUserEvent $event
	 * @deprecated
	 */
    public function onRegistrationInitialize(GetResponseUserEvent $event) //TODO delete. moved to User constructor
    {
        if (!$event->getUser() instanceof User) {
            return;
        }

        $profile = new UserProfile();
        $profile->setProfileVisibleToPublic(true);

        $event->getUser()->setProfile($profile);
    }
	
	public function generateIntroductionEmail(FilterUserResponseEvent $event)
    {
    	$this->logger->info('I am inside generateIntroductionEmail');
    	$em = $this->doctrine->getManager();
    	$user = $event->getUser();
    	$fromuser = $this->userManager->findUserByUsername('noreply');
    	$message = new Message;
    	$message->addRecipient($user);
    	// todo: set the owner of this email to the admin of the website
    	// todo: create a user with id=0 that can be the admin of the website

    	$message->setOwner($fromuser);
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

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $url = $this->router->generate('imdc_profile_me');

        $event->setResponse(new RedirectResponse($url));
    }
}
