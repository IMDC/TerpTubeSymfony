<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Post;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Rest\PostResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PostControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class PostControllerTest extends BaseWebTestCase
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
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestPosts',
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

    public function testNew_GetForm()
    {
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');

        $this->client->request('GET', '/api/v1/posts/new', array(
            'threadId' => $thread->getId(),
            'parentPostId' => -rand()
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('post', $response);
        $this->assertArrayHasKey('form', $response);

        $crawler = new Crawler($response['form']);
        $this->assertCount(1, $crawler->filter('form[name="post"]'), 'a single post form should be present');
    }

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithMediaAndContent()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setParentForum($forum);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());
        $content = 'test:new';

        $this->client->request('GET', '/api/v1/posts/new', array(
            'threadId' => $thread->getId()
        ));
        $this->logResponse(__FUNCTION__, 'form');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $crawler = new Crawler($response['form'], $this->client->getRequest()->getUri());
        $form = $crawler->filter('form[name="post"] > button:contains("Reply")')->form();
        $values = $form->getPhpValues();
        $values['post']['attachedFile'] = $mediaIds;
        $values['post']['content'] = $content;
        $this->client->request($form->getMethod(), '/api/v1/posts?threadId=' . $thread->getId(), $values);
        $this->logResponse(__FUNCTION__, 'result');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('post', $response);
        $this->assertNotNull($response['post']['id']);
        $this->assertArrayHasKey('ordered_media', $response['post']);
        $this->assertMedia($response['post']['ordered_media'], $mediaIds);
    }

    public function testView()
    {
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        /** @var Post $post */
        $post = $this->referenceRepo->getReference('test_post_1');
        $post->setParentThread($thread);
        $post->setContent('test:view');
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/posts/' . $post->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('post', $response);
        $this->assertEquals($post->getId(), $response['post']['id']);
        $this->assertEquals($post->getContent(), $response['post']['content']);
    }

    public function testEdit_GetForm()
    {
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        /** @var Post $post */
        $post = $this->referenceRepo->getReference('test_post_1');
        $post->setParentThread($thread);
        $post->setAuthor($this->loggedInUser);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/v1/posts/' . $post->getId() . '/edit');
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('post', $response);
        $this->assertArrayHasKey('form', $response);

        $crawler = new Crawler($response['form']);
        $this->assertCount(1, $crawler->filter('form[name="post"]'), 'a single post form should be present');
    }

    /**
     * @depends testEdit_GetForm
     */
    public function testEdit_SubmitFormWithMediaAndContent()
    {
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        $thread->setParentForum($forum);
        /** @var Post $post */
        $post = $this->referenceRepo->getReference('test_post_1');
        $post->setParentThread($thread);
        $post->setAuthor($this->loggedInUser);
        $this->entityManager->persist($post);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());
        $content = 'test:edit';

        $this->client->request('GET', '/api/v1/posts/' . $post->getId() . '/edit');
        $this->logResponse(__FUNCTION__, 'form');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $crawler = new Crawler($response['form'], $this->client->getRequest()->getUri());
        $form = $crawler->filter('form[name="post"] > button:contains("Edit")')->form();
        $values = $form->getPhpValues();
        $values['post']['attachedFile'] = $mediaIds;
        $values['post']['content'] = $content;
        $this->client->request($form->getMethod(), '/api/v1/posts/' . $post->getId(), $values);
        $this->logResponse(__FUNCTION__, 'result');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('post', $response);
        $this->assertEquals($post->getId(), $response['post']['id']);
        $this->assertEquals($content, $response['post']['content']);
        $this->assertArrayHasKey('ordered_media', $response['post']);
        $this->assertMedia($response['post']['ordered_media'], $mediaIds);
    }

    public function testDelete()
    {
        /** @var Thread $thread */
        $thread = $this->referenceRepo->getReference('test_thread_1');
        /** @var Post $post */
        $post = $this->referenceRepo->getReference('test_post_1');
        $post->setParentThread($thread);
        $post->setAuthor($this->loggedInUser);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/posts/' . $post->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(PostResponse::OK, $response['code']);
    }
}
