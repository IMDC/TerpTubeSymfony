<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Rest\ForumResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class ForumControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ForumControllerWebTest extends BaseWebTestCase
{
    /**
     * @var User
     */
    private $loggedInUser;

    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUserGroups',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestForums',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));

        $this->client = static::createClient();
        /** @var User $user */
        $user = $this->referenceRepo->getReference('test_user_1');
        Common::login($this->client, $user->getUsername());
        $this->loggedInUser = $user;

        // give logged in user media
        for ($i = 1; $i <= 2; $i++) {
            /** @var Media $media */
            $media = $this->referenceRepo->getReference('test_media_' . $i);
            $media->setOwner($this->loggedInUser);
            $this->entityManager->persist($media);
        }
        $this->entityManager->flush();
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/forum/');
        $this->logResponse(__FUNCTION__);

        $this->assertGreaterThanOrEqual(1, $crawler->filter('p:contains("No forums"), .tt-media-thumbnail')->count(),
            'either "no forums" or forums should be present');
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/forum/new');
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_type_0:checked'),
            '"public" (default) access type should be checked');
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithMedia()
    {
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/forum/new');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertArrayHasKey('ordered_media', $model);
        $this->assertMedia($model['ordered_media'], $mediaIds);
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithTitle()
    {
        $title = 'test:new';

        $crawler = $this->client->request('GET', '/forum/new');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
    }

    public function testNew_GetGroupForm()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/forum/new/' . $group->getId());
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_type_5:checked'),
            '"specific group" access type should be checked');
    }

    /**
     * @depends testNew_GetGroupForm
     */
    public function testNew_SubmitGroupFormWithTitle()
    {
        /** @var UserGroup $group */
        $group = $this->referenceRepo->getReference('test_group_1');
        $group->setUserFounder($this->loggedInUser);
        $this->grantAccessToObject($group, $this->loggedInUser, MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($group);
        $this->entityManager->flush();
        $title = 'test:new';

        $crawler = $this->client->request('GET', '/forum/new/' . $group->getId());
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
        $this->assertEquals($group->getId(), $model['group']['id']);
    }

    public function testView()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');

        $crawler = $this->client->request('GET', '/forum/' . $forum->getId());
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('title:contains("' . $forum->getTitleText() . '")'));
    }

    public function testEdit_GetForm()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $forum->setCreator($this->loggedInUser);
        $this->grantAccessToObject($forum, $this->loggedInUser);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/forum/' . $forum->getId() . '/edit');
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
    }

    /**
     * @depends testEdit_GetForm
     */
    public function testEdit_SubmitFormWithTitle()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $forum->setCreator($this->loggedInUser);
        $this->grantAccessToObject($forum, $this->loggedInUser);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();
        $title = 'test:edit';

        $crawler = $this->client->request('GET', '/forum/' . $forum->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
    }

    public function testEdit_SubmitFormWithMedia()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_2');
        $forum->setCreator($this->loggedInUser);
        $this->grantAccessToObject($forum, $this->loggedInUser);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/forum/' . $forum->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertArrayHasKey('ordered_media', $model);
        $this->assertMedia($model['ordered_media'], $mediaIds);
    }

    public function testDelete()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_3');
        $forum->setCreator($this->loggedInUser);
        $this->grantAccessToObject($forum, $this->loggedInUser);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/forums/' . $forum->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(ForumResponse::OK, $response['code']);
        $this->assertArrayHasKey('redirect_url', $response);
        $this->assertRegExp('/\/forum\/$/', $response['redirect_url']);
    }
}
