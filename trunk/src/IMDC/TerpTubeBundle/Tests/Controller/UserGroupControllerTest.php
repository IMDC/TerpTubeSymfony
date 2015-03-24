<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserGroupControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UserGroupControllerTest extends WebTestCase
{
    private static $userIds = array(13, 14);
    private static $mediaIds = array(4, 1); // shuffle for order check

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        // add users to friends list to bypass invitation step when adding users to a group
        foreach (self::$userIds as $userId) {
            $this->client->request('GET', '/member/friends/' . $userId . '/add');
        }
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/group/');
        $this->assertGreaterThanOrEqual(1, $crawler->filter('p:contains("No groups"), .tt-media-thumbnail')->count(),
            'either "no groups" or groups should be present');
    }

    public function testNew_GetFormWithMembers()
    {
        $users = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->findById(self::$userIds);

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
        $name = 'test:new:' . rand();
        $users = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->findById(self::$userIds);

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

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
        $this->assertCount(count($users) + 1, $crawler->filter('.tt-member-grid-thumbnail')); // owner + members

        $this->delete(Common::getModel($crawler));
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/group/new');

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
        $crawler = $this->client->request('GET', '/group/new');

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['user_group']['name'] = $name;
        $values['user_group']['media'] = self::$mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $model = Common::getModel($crawler);

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));
        $this->assertTrue(is_array($model['ordered_media']));
        $this->assertCount(count(self::$mediaIds), $model['ordered_media']);
        // check existence
        foreach ($model['ordered_media'] as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }
        // check order
        foreach (self::$mediaIds as $key => $mediaId) {
            $this->assertEquals($model['ordered_media'][$key]['id'], $mediaId);
        }

        $this->delete($model);
    }

    /**
     * @depends testNew_GetForm
     * @return array
     */
    public function testNew_SubmitFormWithName()
    {
        $name = 'test:new:' . rand();
        $crawler = $this->client->request('GET', '/group/new');

        $form = $crawler->filter('form[name="user_group"] > button:contains("Create")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));

        return Common::getModel($crawler);
    }

    /**
     * @depends testNew_SubmitFormWithName
     * @param $model
     * @return array
     */
    public function testView($model)
    {
        $crawler = $this->client->request('GET', '/group/' . $model['id']);
        $this->assertCount(1, $crawler->filter('title:contains("' . $model['name'] . '")'));

        return $model;
    }

    /**
     * @depends testView
     * @param $model
     * @return array
     */
    public function testEdit_GetForm($model)
    {
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/edit');
        $this->assertCount(1, $crawler->filter('form[name="user_group"]'), 'a single forum form should be present');

        return $model;
    }

    /**
     * @depends testEdit_GetForm
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithName($model)
    {
        $name = 'test:edit:' . rand();
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/edit');

        $form = $crawler->filter('form[name="user_group"] > div > div > button:contains("Save")')->form(array(
            'user_group[name]' => $name
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $name . '")'));

        return Common::getModel($crawler);
    }

    /**
     * @depends testEdit_SubmitFormWithName
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithMedia($model)
    {
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/edit');

        $form = $crawler->filter('form[name="user_group"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['user_group']['media'] = self::$mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $model = Common::getModel($crawler);

        $this->assertTrue(is_array($model['ordered_media']));
        $this->assertCount(count(self::$mediaIds), $model['ordered_media']);
        // check existence
        foreach ($model['ordered_media'] as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }
        // check order
        foreach (self::$mediaIds as $key => $mediaId) {
            $this->assertEquals($model['ordered_media'][$key]['id'], $mediaId);
        }

        return $model;
    }

    /**
     * @depends testEdit_SubmitFormWithMedia
     * @param $model
     * @return array
     */
    public function testManage_GetForms($model)
    {
        // list style
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/manage?style=list');
        $tableCount = $crawler->filter('.tab-pane[id^=tab]');

        // grid style
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/manage?style=grid');
        $gridCount = $crawler->filter('.tab-pane[id^=tab]');

        // six tables/divs (group members, contacts (all, mentors, meentees, friends), community)
        $this->assertCount(6, $tableCount);
        $this->assertCount(6, $gridCount);
        $this->assertCount(1, $crawler->filter('form[name="ugm_search"]'),
            'a single "ugm_search" form should be present');
        $this->assertCount(2, $crawler->filter('form[name="ugm_remove"], form[name="ugm_add"]'),
            '"users_select" forms should be present');

        return $model;
    }

    /**
     * @depends testManage_GetForms
     * @param $model
     * @return array
     */
    public function testManage_SearchCommunity($model)
    {
        $user = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->find(self::$userIds[0]);
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/manage?style=list');

        $form = $crawler->filter('form[name="ugm_search"]')->form(array(
            'ugm_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('.tab-pane[id=tabCommunity] [data-uid="' . $user->getId() . '"]'),
            'user should be present');

        return $model;
    }

    /**
     * @depends testManage_SearchCommunity
     * @param $model
     * @return array
     */
    public function testManage_AddMembers($model)
    {
        $groupId = $model['id'];
        $crawler = $this->client->request('GET', '/group/' . $groupId . '/manage');

        $form = $crawler->filter('form[name="ugm_add"]')->form();
        $values = $form->getPhpValues();
        $values['ugm_add']['users'] = self::$userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->entityManager->clear();
        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        $users = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->findById(self::$userIds);
        foreach ($users as $user) {
            $this->assertTrue($group->getMembers()->contains($user), 'group should contain user as member');
        }

        return $model;
    }

    /**
     * @depends testManage_AddMembers
     * @param $model
     * @return array
     */
    public function testManage_SearchMembers($model)
    {
        $user = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->find(self::$userIds[1]);
        $crawler = $this->client->request('GET', '/group/' . $model['id'] . '/manage?style=list');

        $form = $crawler->filter('form[name="ugm_search"]')->form(array(
            'ugm_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('.tab-pane[id=tabMembers] [data-uid="' . $user->getId() . '"]'),
            'user should be present');

        return $model;
    }

    /**
     * @depends testManage_SearchMembers
     * @param $model
     * @return array
     */
    public function testManage_RemoveMembers($model)
    {
        $groupId = $model['id'];
        $crawler = $this->client->request('GET', '/group/' . $groupId . '/manage');

        $form = $crawler->filter('form[name="ugm_remove"]')->form();
        $values = $form->getPhpValues();
        $values['ugm_remove']['users'] = self::$userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->entityManager->clear();
        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        $users = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->findById(self::$userIds);
        foreach ($users as $user) {
            $this->assertFalse($group->getMembers()->contains($user), 'group should not contain user as member');
        }

        return $model;
    }

    /**
     * @depends testManage_RemoveMembers
     * @param $model
     * @return array
     */
    public function testDelete($model)
    {
        $this->client->request('POST', '/group/' . $model['id'] . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasDeleted', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertTrue($response['wasDeleted']);
        $this->assertRegExp('/\/group\/$/', $response['redirectUrl']);
    }

    private function delete($model)
    {
        // manually delete the group
        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find($model['id']);
        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }
}
