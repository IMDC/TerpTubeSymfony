<?php

namespace IMDC\TerpTubeBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use IMDC\TerpTubeBundle\Definition\MultiPagination;
use IMDC\TerpTubeBundle\Entity;
use IMDC\TerpTubeBundle\Form\DataTransformer\UserCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
use IMDC\TerpTubeBundle\Rest\Exception\ContactException;
use IMDC\TerpTubeBundle\Rest\RestResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContactController
 *
 * @Rest\NoRoute()
 *
 * @package IMDC\TerpTubeBundle\Controller
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ContactController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $style = $this->get('request')->query->get('style', 'list');

        // pagination
        $defaultPageNum = 1;
        $defaultPageLimit = 24;
        $pages = self::getPaginationPages($defaultPageNum, $defaultPageLimit, $style);

        $user = $this->getUser();
        $mentors = $user->getMentorList()->toArray();
        $mentees = $user->getMenteeList()->toArray();
        $friends = $user->getFriendsList()->toArray();
        $all = array_unique(array_merge($mentors, $mentees, $friends), SORT_REGULAR);

        // pagination
        /* @var $paginator MultiPagination */
        $paginator = $this->get('imdc_terptube.definition.multi_pagination');
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

    public static function getPaginationPages($defaultPageNum, $defaultPageLimit, $style)
    {
        return array(
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
    }

    /**
     * @Rest\Route()
     * @Rest\View()
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request)
    {
        $userIds = $request->get('userIds', array());
        $contactList = strtolower((string)$request->get('contactList'));
        if (empty($contactList)) {
            ContactException::BadRequest(ContactException::MESSAGE_INVALID_LIST);
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
                    $contact->getMenteeList()->removeElement($user);
                    $contact->getMentorList()->removeElement($user);
                    $contact->getFriendsList()->removeElement($user);
                    break;
                case 'mentors':
                    $user->getMentorList()->removeElement($contact);
                    $contact->getMenteeList()->removeElement($user);
                    break;
                case 'mentees':
                    $user->getMenteeList()->removeElement($contact);
                    $contact->getMentorList()->removeElement($user);
                    break;
                case 'friends':
                    $user->getFriendsList()->removeElement($contact);
//                         $contact->getFriendsList()->removeElement($user);
                    break;
                default:
                    ContactException::BadRequest(ContactException::MESSAGE_INVALID_LIST);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->view(new RestResponse());
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
