<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity;
use IMDC\TerpTubeBundle\Form\DataTransformer\UserCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
use IMDC\TerpTubeBundle\Helper\MultiPaginationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContactController
 * @package IMDC\TerpTubeBundle\Controller
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ContactController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        // check if user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $style = $this->get('request')->query->get('style', 'list');

        // pagination
        $defaultPageNum = 1;
        $defaultPageLimit = 24;
        $pages = array(
            'mentors' => array(
                'knp' => array('pageParameterName' => 'page_r'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style
                )
            ),
            'mentees' => array(
                'knp' => array('pageParameterName' => 'page_e'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style
                )
            ),
            'friends' => array(
                'knp' => array('pageParameterName' => 'page_s'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style
                )
            ),
            'all' => array(
                'knp' => array('pageParameterName' => 'page_l'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style
                )
            )
        );

        $user = $this->getUser();
        $mentors = $user->getMentorList()->toArray();
        $mentees = $user->getMenteeList()->toArray();
        $friends = $user->getFriendsList()->toArray();
        $all = array_merge($mentors, $mentees, $friends);

        // pagination
        /* @var $paginator MultiPaginationHelper */
        $paginator = $this->get('imdc_terptube.helper.multi_pagination_helper');
        $paginator->setPages($pages);
        $paginator->prepare($request);

        $mentors = $paginator->paginate('mentors', $mentors);
        $mentees = $paginator->paginate('mentees', $mentees);
        $friends = $paginator->paginate('friends', $friends);
        $all = $paginator->paginate('all', $all);

        $usersSelectForm = $this->createForm(new UsersSelectType(), null, array(
            'em' => $this->getDoctrine()->getManager()
        ));

        return $this->render('IMDCTerpTubeBundle:Contact:list.html.twig', array(
            'style' => $style,
            'mentors' => $mentors,
            'mentees' => $mentees,
            'friends' => $friends,
            'all' => $all,
            'users_select_form' => $usersSelectForm->createView()
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction(Request $request) //TODO api?
    {
        // check if user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        try {
            $userIds = $request->get('userIds', array());
            $contactList = strtolower((string)$request->get('contactList'));
            if (empty($contactList)) {
                throw new \Exception('contact list must not be empty');
            }

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();

            $transformer = new UserCollectionToIntArrayTransformer($em);
            $contacts = $transformer->reverseTransform($userIds);

            foreach ($contacts as $contact) {
                switch ($contactList) {
                    case 'all':
                        $user->getMentorList()->removeElement($contact);
                        $user->getMenteeList()->removeElement($contact);
                        $user->getFriendsList()->removeElement($contact);
                        break;
                    case 'mentor':
                        $user->getMentorList()->removeElement($contact);
                        break;
                    case 'mentee':
                        $user->getMenteeList()->removeElement($contact);
                        break;
                    case 'friends':
                        $user->getFriendsList()->removeElement($contact);
                        break;
                    default:
                        throw new \Exception('invalid contact list');
                }
            }

            $em->persist($user);
            $em->flush();

            $content = array(
                'success' => true
            );
        } catch (\Exception $ex) {
            $content = array(
                'success' => false,
                'message' => $ex->getMessage()
            );
        }

        return new Response (json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * Add a user to the currently logged in user's friendlist
     *
     * @param Request $request
     * @param unknown $userid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, $userid)
    {

        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = new \IMDC\TerpTubeBundle\Entity\User;

        $user = $this->getUser();

        $userManager = $this->container->get('fos_user.user_manager');

        $usertoadd = $userManager->findUserBy(array('id' => $userid));

        $user->addFriendsList($usertoadd);

        // flush object to database
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('imdc_profile_user',
            array('userName' => $usertoadd->getUserName())));
    }

    /**
     * Remove a user from a friendslist
     *
     * @param Request $request
     * @param integer $userid
     * @param string $redirect where to send the user after removed
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @deprecated
     */
    public function removeAction(Request $request, $userid, $redirect)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = new \IMDC\TerpTubeBundle\Entity\User;

        $user = $this->getUser();

        $userManager = $this->container->get('fos_user.user_manager');

        $usertoremove = $userManager->findUserBy(array('id' => $userid));

        $user->removeFriendsList($usertoremove);

        // flush object to database
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        if ($redirect == NULL) {
            return $this->redirect($this->generateUrl('imdc_profile_user',
                array('userName' => $usertoremove->getUserName()))
            );
        }

        return $this->redirect($this->generateUrl($redirect));
    }
}