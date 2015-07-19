<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Rest\ThreadResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;

/**
 * Class ThreadControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ThreadControllerTest extends BaseWebTestCase
{
    /**
     * @var User
     */
    private $loggedInUser;

    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestForums',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestThreads',
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

    public function testNew_GetFormWithMedia()
    {
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/thread/new/myFiles/' . $mediaIds[0]);
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');
        $this->assertCount(1, $crawler->filter('[name^="thread[forum]"]'), 'forum field should be present');
        $this->assertCount(1, $crawler->filter('[name^="thread[mediaIncluded]"][value="' . $mediaIds[0] . '"]'),
            'media should be present');
        $this->assertCount(1, $crawler->filter('#thread_accessType_type_0:checked'),
            '"public" access type should be checked');
    }

    public function testNew_GetForm()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');

        $crawler = $this->client->request('GET', '/thread/new/' . $forum->getId());
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');
        $this->assertCount(1, $crawler->filter('#thread_accessType_type_0:checked'),
            '"public" access type should be checked');
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithMedia()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $crawler = $this->client->request('GET', '/thread/new/' . $forum->getId());
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="thread"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['thread']['mediaIncluded'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $model = Common::getModel($crawler);

        $this->assertTrue(is_array($model['ordered_media']));
        $this->assertCount(count($mediaIds), $model['ordered_media']);
        $this->assertMedia($model['ordered_media'], $mediaIds);
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithTitle()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $title = 'test:new';

        $crawler = $this->client->request('GET', '/thread/new/' . $forum->getId());
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="thread"] > button:contains("Create")')->form(array(
            'thread[title]' => $title
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
    }

    public function testView()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setParentForum($forum);
        $thread->setCreator($this->loggedInUser);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/thread/' . $thread->getId());
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('title:contains("' . $thread->getTitle() . '")'));
    }

    public function testEdit_GetForm()
    {
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setCreator($this->loggedInUser);
        $this->grantAccessToObject($thread, $this->loggedInUser);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/thread/' . $thread->getId() . '/edit');
        $this->logResponse(__FUNCTION__);

        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');
    }

    /**
     * @depends testEdit_GetForm
     */
    public function testEdit_SubmitFormWithTitle()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setParentForum($forum);
        $thread->setCreator($this->loggedInUser);
        $this->grantAccessToObject($thread, $this->loggedInUser);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();
        $title = 'test:edit';

        $crawler = $this->client->request('GET', '/thread/' . $thread->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');

        $form = $crawler->filter('form[name="thread"] > div > div > button:contains("Save")')->form(array(
            'thread[title]' => $title
        ));
        $this->client->submit($form);
        $this->logResponse(__FUNCTION__, 'result');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
    }

    public function testDelete()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setParentForum($forum);
        $thread->setCreator($this->loggedInUser);
        $this->grantAccessToObject($thread, $this->loggedInUser);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/threads/' . $thread->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(ThreadResponse::OK, $response['code']);
        $this->assertRegExp('/\/forum\/\d+$/', $response['redirect_url']);
    }
}
