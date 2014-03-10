<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Form\Type\InvitationType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use IMDC\TerpTubeBundle\Entity\Message;

/**
 * Invitation controller.
 *
 */
class InvitationController extends Controller
{

    /**
     * Lists all Invitation entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('IMDCTerpTubeBundle:Invitation')->findAll();

        return $this->render('IMDCTerpTubeBundle:Invitation:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Invitation entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Invitation();
        $user = $this->getUser();
        $entity->setCreator($user);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if ($entity->getCreator() === $entity->getRecipient()) {
                $this->get('session')->getFlashBag()->add('error', 'You cannot send an invitation to yourself');
                return $this->render('IMDCTerpTubeBundle:Invitation:new.html.twig', array(
                        'entity' => $entity,
                        'form'   => $form->createView(),
                ));
            }
            
            $em->persist($entity);
            
            $recip = $entity->getRecipient();
            
            $user->addCreatedInvitation($entity);
            $recip->addReceivedInvitation($entity);
            
            $em->persist($user);
            $em->persist($recip);
            
            $em->flush();

            return $this->redirect($this->generateUrl('invitation_show', array('id' => $entity->getId())));
        }

        return $this->render('IMDCTerpTubeBundle:Invitation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Invitation entity.
    *
    * @param Invitation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Invitation $entity)
    {
        $form = $this->createForm(new InvitationType(), $entity, array(
            'action' => $this->generateUrl('invitation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    public function createMentorInvitationAction(Request $request, $id) 
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $userRecipient = $userManager->findUserBy(array('id' => $id));
        
        if (!$userRecipient) {
            throw $this->createNotFoundException('Unable to find user.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        $entity = new Invitation();
        $entity->setCreator($user);
        $entity->setRecipient($userRecipient);
        $entity->setBecomeMentor(true);
        
        $user->addCreatedInvitation($entity);
        $userRecipient->addReceivedInvitation($entity);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($userRecipient);
        $em->persist($entity);
        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('success', 'Invitation sent');
        
        $url = $this->getRequest()->headers->get("referer");
        return new RedirectResponse($url);
    }
    
    public function createMenteeInvitation(Request $request, $id) 
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $userRecipient = $userManager->findUserBy(array('id' => $id));
        
        if (!$userRecipient) {
            throw $this->createNotFoundException('Unable to find user.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        $entity = new Invitation();
        $entity->setCreator($user);
        $entity->setRecipient($userRecipient);
        $entity->setBecomeMentee(true);
        
        $user->addCreatedInvitation($entity);
        $userRecipient->addReceivedInvitation($entity);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($userRecipient);
        $em->persist($entity);
        
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('success', 'Invitation sent');
        
        $url = $this->getRequest()->headers->get("referer");
        return new RedirectResponse($url);
    }
    
    /**
     * Displays a form to create a new Invitation entity.
     *
     */
    public function newAction()
    {
        $entity = new Invitation();
        $entity->setCreator($this->getUser());
        $form   = $this->createCreateForm($entity);

        return $this->render('IMDCTerpTubeBundle:Invitation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Invitation entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('IMDCTerpTubeBundle:Invitation:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Invitation entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('IMDCTerpTubeBundle:Invitation:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Invitation entity.
    *
    * @param Invitation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Invitation $entity)
    {
        $form = $this->createForm(new InvitationType(), $entity, array(
            'action' => $this->generateUrl('invitation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Invitation entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('invitation_edit', array('id' => $id)));
        }

        return $this->render('IMDCTerpTubeBundle:Invitation:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Completely deletes a Invitation entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Invitation entity.');
            }

            $user = $this->getUser();
            $recip = $entity->getRecipient();
            
            // remove the invitation from the user's created invitation list
            $user->removeCreatedInvitation($entity);
            // remove the invitation from the recipient's received invitations list
            $recip->removeReceivedInvitation($entity);
            
            $em->persist($user);

            $em->remove($entity);
            
            $em->flush();
        }

        return $this->redirect($this->generateUrl('invitation'));
    }
    
    /**
     * Cancel an existing invitation as long as you are the user who created it
     * 
     * @param Request $request
     * @param integer $id The id of the invitation to cancel
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cancelAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        // check to make sure the invitation is not yet accepted
        if ($entity->getIsAccepted()) {
            $this->get('session')->getFlashBag()->add('error', 'Invitation has already been accepted and cannot be cancelled.');
            return $this->render('IMDCTerpTubeBundle:Invitation:userOverview.html.twig');
            
        }
        
        $user = $this->getUser();
        
        // make sure user is the creator of this invitation
        if (!$user === $entity->getCreator()) {
            throw new AccessDeniedException('You do not have permission to cancel this invitation');
        }
        
        $entity->setIsCancelled(true);
        $em->persist($entity);
        $em->flush();
    
        return $this->render('IMDCTerpTubeBundle:Invitation:userOverview.html.twig');
    }

    /**
     * Creates a form to delete a Invitation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('invitation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * Creates a form to cancel an Invitation entity by id.
     * 
     * @param mixed $id The entity id
     * 
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCancelForm($id)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('imdc_invitation_cancel', array('id' => $id)))
        ->setMethod('PUT')
        ->add('submit', 'submit', array('label' => 'Cancel Invitation'))
        ->getForm()
        ;
    }
    
    public function listAction(Request $request) 
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        return $this->render('IMDCTerpTubeBundle:Invitation:userOverview.html.twig');
        
    }
    
    public function reactivateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        // make sure user is the creator of this invitation
        if (!$user === $entity->getCreator()) {
            throw new AccessDeniedException('You do not have permission to reactivate this invitation');
        }
        
        $entity->setIsCancelled(false);
        $em->persist($entity);
        $em->flush();
        
        return $this->redirect($this->generateUrl('imdc_invitations_list'));
        
    }
    
    public function acceptAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        // check to make sure this user is the target of the invitation
        if (!$user === $entity->getRecipient()) {
            throw new AccessDeniedException('You do not have permission to accept this invitation');
        }
        
        // check to make sure the invitation is not cancelled or declined
        if ($entity->getIsCancelled() || $entity->getIsDeclined()) {
            throw new \LogicException('Invitation cannot be accepted');
        }
        
        // deal with the actions of the invitation
        if ($entity->getBecomeMentee()) {
            // recipient becomes mentee of the invitation creator
            $entity->getCreator()->addMenteeList($entity->getRecipient());
            // sender/creator becomes mentor to the recipient
            $entity->getRecipient()->addMentorList($entity->getCreator());
            
            // send message to invitation creator?
            $noReplyUser = $em->getRepository('IMDCTerpTubeBundle:User')->findNoReplyUser();
            
            $messageSubject = $entity->getRecipient()->getUsername() . ' has accepted your invitation';
            $messageContent = 'Congratulations, ' . $entity->getRecipient()->getUsername() . ' has accepted your invitation to become your mentee!';
            
            $message = $entity->getCreator()->createMessageToUser($noReplyUser, $messageSubject, $messageContent);
            $em->persist($message);
            
        }
        elseif ($entity->getBecomeMentor()) {
            // recipient becomes mentor of the invitation creator
            $entity->getCreator()->addMentorList($entity->getRecipient());
            // sendor/creates becomes mentee to the recipient
            $entity->getRecipient()->addMenteeList($entity->getCreator());
            
            // send message to invitation creator?
            $noReplyUser = $em->getRepository('IMDCTerpTubeBundle:User')->findOneBy(array('id' => 0));
            
            $messageSubject = $entity->getRecipient()->getUsername() . ' has accepted your invitation';
            $messageContent = 'Congratulations, ' . $entity->getRecipient()->getUsername() . ' has accepted your invitation to become your mentor!';
            
            $message = $entity->getCreator()->createMessageToUser($noReplyUser, $messageSubject, $messageContent);
            $em->persist($message);
        }
        
        $entity->setIsAccepted(true);
        $entity->setIsDeclined(false);
        $entity->setIsCancelled(false);
        
        $em->persist($entity);
        $em->persist($user);
        $em->persist($entity->getCreator());
        
        $em->flush();
        
        return $this->redirect($this->generateUrl('imdc_invitations_list'));
        
    }
    
    public function declineAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('IMDCTerpTubeBundle:Invitation')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }
        
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
        {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $user = $this->getUser();
        
        // check to make sure this user is the target of the invitation
        if (!$user === $entity->getRecipient()) {
            throw new AccessDeniedException('You do not have permission to decline this invitation');
        }
        
        // check to make sure invitation is not cancelled first
        if ($entity->getIsCancelled()) {
            throw new \LogicException('Invitation was cancelled');
        }
        
        $entity->setIsAccepted(false);
        $entity->setIsDeclined(true);
        $entity->setDateDeclined(new \DateTime('now'));
        $em->persist($entity);
        $em->flush();
        
        return $this->redirect($this->generateUrl('imdc_invitations_list'));
    }
}
