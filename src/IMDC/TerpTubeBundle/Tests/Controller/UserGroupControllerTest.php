<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
use IMDC\TerpTubeBundle\Rest\UserGroupResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class UserGroupControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UserGroupControllerTest extends BaseWebTestCase
{
    /**
     * @var User
     */
    private $loggedInUser;

    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUserGroups'
        ));

        $this->client = static::createClient();
        /** @var User $user1 */
        $user1 = $this->referenceRepo->getReference('test_user_1');
        Common::login($this->client, $user1->getUsername());
        $this->loggedInUser = $user1;

        // add users to friends list
        $user2 = $this->referenceRepo->getReference('test_user_2');
        $user3 = $this->referenceRepo->getReference('test_user_3');

        $user1->addFriendsList($user2);
        $user1->addFriendsList($user3);

        $this->entityManager->persist($user1);
        $this->entityManager->flush();
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/group/');
        $this->logResponse(__FUNCTION__);

        $this->assertGreaterThanOrEqual(1, $crawler->filter('p:contains("No groups"), .tt-media-thumbnail')->count(),
            'either "no groups" or groups should be present');
    }

    public function testNew_GetFormWithMembers()
    {
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');
        $users = array($user2, $user3);

        $form = $this->client->getContainer()
            ->get('form.factory')
            ->create(new UsersSelectType(), null, array('em' => $this->entityManager));
        $form->get('users')->setData($users);
        $formView = $form->createView();

        $values = array();
        Common::formViewToPhpValues($formView, $values);

        $token = $this->client->getContainer()
            ->get('form.csrf_provider')
            ->generateCsrfToken($form->getName());
        $values[$form->getName()]['_token'] = $token;

        $crawler = $this->client->request('POST', '/group/new', $values);
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="user_group"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('input[name="user_group[visibleToRegisteredUsers]"]:checked'),
            'option should be checked');
        $this->assertCount(1, $crawler->filter('input[name="user_group[openForNewMembers]"]:checked'),
            'option should be checked');
        foreach ($users as $user) {
            $this->assertCount(1, $crawler->filter('input[name="user_group[members]"][value*="' . $user->getUsername() . '"]'),
                'user should be present');
        }
    }

    /**
     * @depends testNew_GetFormWithMembers
     */
    public function testNew_SubmitFormWithNameAndMembers()
    {
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');
        $users = array($user2, $user3);
        $name = 'test:new:' . rand();

        // members field is only available when user ids are posted
        $form = $this->client->getContainer()
            ->get('form.factory')
            ->create(new UsersSelectType(), null, array('em' => $this->entityManager));
        $form->get('users')->setData($users);
        $formView = $form->createView();

        $values = array();
        Common::formViewToPhpValues($formView, $values);

        $token = $this->client->getContainer()
            ->get('form.csrf_provider')
            ->generateCsrfToken($form->getName());
        $values[$form->getName()]['_token'] = $token;

        $crawler = $this->client->request('POST', '/group/new', $values);
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
        $this->assertCount(count($users) + 1, $crawler->filter('.tt-member-grid-thumbnail')); // owner + members
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/group/new');
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="user_group"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('input[name="user_group[visibleToRegisteredUsers]"]:checked'),
            'option should be checked');
        $this->assertCount(1, $crawler->filter('input[name="user_group[openForNewMembers]"]:checked'),
            'option should be checked');
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithMedia()
    {
        $name = 'test:new:' . rand();
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/group/new');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['user_group']['name'] = $name;
        $values['user_group']['media'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
        $this->assertArrayHasKey('ordered_media', $model);
        $this->assertMedia($model['ordered_media'], $mediaIds);
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithName()
    {
        $name = 'test:new:' . rand();

        $crawler = $this->client->request('GET', '/group/new');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
    }

    public function testView()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');

        $crawler = $this->client->request('GET', '/group/' . $group->getId());
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('title:contains("' . $group->getName() . '")'));
    }

    public function testEdit_GetForm()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/edit');
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="user_group"]'), 'a single forum form should be present');
    }

    /**
     * @depends testEdit_GetForm
     */
    public function testEdit_SubmitFormWithName()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        $name = 'test:edit:' . rand();

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="user_group"] > div > div > button:contains("Save")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
    }

    public function testEdit_SubmitFormWithMedia()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="user_group"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['user_group']['media'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertArrayHasKey('ordered_media', $model);
        $this->assertMedia($model['ordered_media'], $mediaIds);
    }

    public function testManage_GetForms()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        // list style
        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage?style=list');
        $this->logResponse(__FUNCTION__, 'list');
        $tableCount = $crawler->filter('.tab-pane[id^=tab]');

        // grid style
        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage?style=grid');
        $this->logResponse(__FUNCTION__, 'grid');
        $gridCount = $crawler->filter('.tab-pane[id^=tab]');

        // six tables/divs (group members, contacts (all, mentors, meentees, friends), community)
        $this->assertCount(6, $tableCount);
        $this->assertCount(6, $gridCount);
        $this->assertCount(1, $crawler->filter('form[name="ugm_search"]'),
            'a single "ugm_search" form should be present');
        $this->assertCount(2, $crawler->filter('form[name="ugm_remove"], form[name="ugm_add"]'),
            '"users_select" forms should be present');
    }

    /**
     * @depend testManage_GetForms
     */
    public function testManage_SearchCommunity()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        /** @var User $user */
        $user = $this->referenceRepo->getReference('test_user_4');

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage?style=list');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="ugm_search"]')->form(array(
            'ugm_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertCount(1, $crawler->filter('.tab-pane[id=tabCommunity] [data-uid="' . $user->getId() . '"]'),
            'user should be present');
    }

    /**
     * @depends testManage_GetForms
     */
    public function testManage_AddMembers()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');
        $users = array($user2, $user3);
        $userIds = array($user2->getId(), $user3->getId());

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="ugm_add"]')->form();
        $values = $form->getPhpValues();
        $values['ugm_add']['users'] = $userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->entityManager->refresh($group);
        foreach ($users as $user) {
            $this->entityManager->refresh($user);
            $this->assertTrue($group->getMembers()->contains($user), 'group should contain user as member');
        }
    }

    /**
     * @depends testManage_GetForms
     */
    public function testManage_SearchMembers()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        /** @var User $user */
        $user = $this->referenceRepo->getReference('test_user_4');
        $user->addUserGroup($group);
        $this->entityManager->persist($user);
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage?style=list');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="ugm_search"]')->form(array(
            'ugm_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertCount(1, $crawler->filter('.tab-pane[id=tabMembers] [data-uid="' . $user->getId() . '"]'),
            'user should be present');
    }

    /**
     * @depends testManage_GetForms
     */
    public function testManage_RemoveMembers()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        $user2->addUserGroup($group);
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');
        $user3->addUserGroup($group);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($user3);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        $users = array($user2, $user3);
        $userIds = array($user2->getId(), $user3->getId());

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/manage');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="ugm_remove"]')->form();
        $values = $form->getPhpValues();
        $values['ugm_remove']['users'] = $userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->entityManager->refresh($group);
        foreach ($users as $user) {
            $this->entityManager->refresh($user);
            $this->assertFalse($group->getMembers()->contains($user), 'group should not contain user as member');
        }
    }

    public function testDelete()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/groups/' . $group->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(UserGroupResponse::OK, $response['code']);
        $this->assertArrayHasKey('redirect_url', $response);
        $this->assertRegExp('/\/group\/$/', $response['redirect_url']);
    }
}
