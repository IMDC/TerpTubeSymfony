<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ForumControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ForumControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/forum/new');

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_0:checked'), '"public" (default) access type should be checked');
    }

    public function testNew_SubmitFormWithTitle()
    {
        $title = 'test:new';
        $crawler = $this->client->request('GET', '/forum/new');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
    }

    public function testNew_SubmitFormWithMedia()
    {
        $mediaIds = array(1);
        $crawler = $this->client->request('GET', '/forum/new');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        //echo $this->client->getResponse()->getContent(); die;

        $media = json_decode($crawler->filter('#__testMedia')->text(), true);

        $this->assertTrue(is_array($media));
        $this->assertGreaterThanOrEqual(1, count($media));
        $this->assertArrayHasKey('id', $media[0]);
        $this->assertEquals($mediaIds[0], $media[0]['id']);
    }

    public function testNew_GetGroupForm()
    {
        $crawler = $this->client->request('GET', '/forum/new/4');

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_4:checked'), '"specific group" access type should be checked');
    }

    public function testNew_SubmitGroupFormWithTitle()
    {
        $groupId = 4;
        $title = 'test:new';
        $crawler = $this->client->request('GET', '/forum/new/' . $groupId);

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
        $this->assertEquals($groupId, $crawler->filter('#__testGroupId')->text());

        preg_match('/\/forum\/(\d+)/', $this->client->getRequest()->getUri(), $matches);
        return $matches[1];
    }

    /**
     * @depends testNew_SubmitGroupFormWithTitle
     * @param $forumId
     */
    public function testEdit_GetForm($forumId)
    {
        $crawler = $this->client->request('GET', '/forum/' . $forumId . '/edit');
        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');

        return $forumId;
    }

    /**
     * @depends testEdit_GetForm
     * @param $forumId
     */
    public function testEdit_SubmitFormWithTitle($forumId)
    {
        $title = 'test:edit';
        $crawler = $this->client->request('GET', '/forum/' . $forumId . '/edit');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));

        return $forumId;
    }

    /**
     * @depends testEdit_SubmitFormWithTitle
     * @param $forumId
     */
    public function testEdit_SubmitFormWithMedia($forumId)
    {
        $mediaIds = array(4);
        $crawler = $this->client->request('GET', '/forum/' . $forumId . '/edit');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = $mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $media = json_decode($crawler->filter('#__testMedia')->text(), true);

        $this->assertTrue(is_array($media));
        $this->assertGreaterThanOrEqual(1, count($media));
        $this->assertArrayHasKey('id', $media[0]);
        $this->assertEquals($mediaIds[0], $media[0]['id']);
    }
}
