<?php

namespace IMDC\TerpTubeBundle\Controller;

use IMDC\TerpTubeBundle\Entity;
use IMDC\TerpTubeBundle\Form\DataTransformer\UserCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
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

        // pagination
        $defaultPageNum = 1;
        $defaultPageLimit = 24;
        $paginatorParams = array(
            'all' => array(
                'knp' => array('pageParameterName' => 'page_l'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit
            ),
            'mentors' => array(
                'knp' => array('pageParameterName' => 'page_r'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit
            ),
            'mentees' => array(
                'knp' => array('pageParameterName' => 'page_e'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit
            ),
            'friends' => array(
                'knp' => array('pageParameterName' => 'page_s'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit
            )
        );
        //TODO consolidate?
        // extract paginator params from request
        foreach ($paginatorParams as &$params) {
            $params['page'] = $request->query->get($params['knp']['pageParameterName'], $params['page']);
        }

        $user = $this->getUser();
        $all = array_merge($user->getMentorList()->toArray(),
            $user->getMenteeList()->toArray(),
            $user->getFriendsList()->toArray());

        // pagination
        $paginator = $this->get('knp_paginator');
        //TODO consolidate?
        $paginate = function ($object, $name) use ($paginatorParams, $paginator) {
            $params = $paginatorParams[$name];

            $paginated = $paginator->paginate(
                $object,
                $params['page'],
                $params['pageLimit'],
                $params['knp']
            );

            if (array_key_exists('urlParams', $params)) {
                foreach ($params['urlParams'] as $key => $value) {
                    $paginated->setParam($key, $value);
                }
            }

            return $paginated;
        };

        $all = $paginate($all, 'all');
        $mentors = $paginate($user->getMentorList(), 'mentors');
        $mentees = $paginate($user->getMenteeList(), 'mentees');
        $friends = $paginate($user->getFriendsList(), 'friends');

        $usersSelectForm = $this->createForm(new UsersSelectType(), null, array(
            'em' => $this->getDoctrine()->getManager()
        ));

        return $this->render('IMDCTerpTubeBundle:Contact:list.html.twig', array(
            'all' => $all,
            'mentors' => $mentors,
            'mentees' => $mentees,
            'friends' => $friends,
            'users_select_form' => $usersSelectForm->createView()
        ));
    }

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

    /**
     * Show all people on friends list
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @deprecated
     */
    public function showAllAction(Request $request)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = new \IMDC\TerpTubeBundle\Entity\User;

        $user = $this->getUser();

        $usersFriends = $user->getFriendsList();

        return $this->render('IMDCTerpTubeBundle:Member:index.html.twig', array(
            'members' => $usersFriends,
            'isFriendsList' => true
        ));
    }

}
