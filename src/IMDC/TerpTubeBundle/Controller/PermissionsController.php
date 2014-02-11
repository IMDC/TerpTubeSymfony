<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use IMDC\TerpTubeBundle\Entity\Permissions;
use IMDC\TerpTubeBundle\Form\Type\PermissionsType;

/**
 * Permissions controller.
 *
 */
class PermissionsController extends Controller
{

    /**
     * Lists all Permissions entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('IMDCTerpTubeBundle:Permissions')->findAll();

        return $this->render('IMDCTerpTubeBundle:Permissions:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Permissions entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Permissions();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('permissions_show', array('id' => $entity->getId())));
        }

        return $this->render('IMDCTerpTubeBundle:Permissions:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Permissions entity.
    *
    * @param Permissions $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Permissions $entity)
    {
        $form = $this->createForm(new PermissionsType(), $entity, array(
            'action' => $this->generateUrl('permissions_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Permissions entity.
     *
     */
    public function newAction()
    {
        $entity = new Permissions();
        $form   = $this->createCreateForm($entity);

        return $this->render('IMDCTerpTubeBundle:Permissions:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Permissions entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Permissions')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Permissions entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('IMDCTerpTubeBundle:Permissions:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Permissions entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Permissions')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Permissions entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('IMDCTerpTubeBundle:Permissions:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Permissions entity.
    *
    * @param Permissions $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Permissions $entity)
    {
        $form = $this->createForm(new PermissionsType(), $entity, array(
            'action' => $this->generateUrl('permissions_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Permissions entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IMDCTerpTubeBundle:Permissions')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Permissions entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('permissions_edit', array('id' => $id)));
        }

        return $this->render('IMDCTerpTubeBundle:Permissions:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Permissions entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('IMDCTerpTubeBundle:Permissions')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Permissions entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('permissions'));
    }

    /**
     * Creates a form to delete a Permissions entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('permissions_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}