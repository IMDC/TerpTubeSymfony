<?php

namespace IMDC\TerpTubeBundle\EventListener;
	
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use IMDC\TerpTubeBundle\Entity\UserProfile;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use FOS\UserBundle\Event\GetResponseGroupEvent;
use FOS\UserBundle\Event\FilterGroupResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

class GroupListener implements EventSubscriberInterface
{
    private $logger;
    private $doctrine;
    private $router;

    public function __construct($logger, $doctrine, $router, SecurityContext $context)
    {
        $this->logger   = $logger;
        $this->doctrine = $doctrine;
        $this->router   = $router;
        $this->context  = $context;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                FOSUserEvents::GROUP_CREATE_COMPLETED=> 'assignGroupFounder',
        );
    }
    
    public function assignGroupFounder(FilterGroupResponseEvent $event)
    {
        $user = $this->context->getToken()->getUser();
        
        $group = $event->getGroup();
        $ugroup = $group->getName();
        
        $ufname = $user->getProfile()->getFirstName();
        
        $this->logger->info("I'm inside assignGroupFinder, $ufname is the current user, $ugroup is the group name");
        
        $group->setUserFounder($user);
        
        $em = $this->doctrine->getManager();
        $em->persist($group);
        $em->flush();
        
        $url = $this->router->generate('fos_user_group_list');
        
        $event->setResponse(new RedirectResponse($url));
    }
    
}
