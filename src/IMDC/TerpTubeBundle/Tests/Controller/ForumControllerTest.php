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
    private static $mediaIds = array(4, 1); // shuffle for order check

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/forum/');
        $this->assertGreaterThanOrEqual(1, $crawler->filter('p:contains("No forums"), .tt-forum-thumbnail')->count(),
            'either "no forums" or forums should be present');
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/forum/new');

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_0:checked'),
            '"public" (default) access type should be checked');
    }

    /**
     * @depends testNew_GetForm
     */
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

    /**
     * @depends testNew_GetForm
     */
    public function testNew_SubmitFormWithMedia()
    {
        $crawler = $this->client->request('GET', '/forum/new');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = self::$mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $media = json_decode($crawler->filter('#__testMedia')->text(), true);

        $this->assertTrue(is_array($media));
        $this->assertCount(count(self::$mediaIds), $media);
        foreach ($media as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }
    }

    public function testNew_GetGroupForm()
    {
        $crawler = $this->client->request('GET', '/forum/new/4');

        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');
        $this->assertCount(1, $crawler->filter('#forum_accessType_4:checked'),
            '"specific group" access type should be checked');
    }

    /**
     * @depends testNew_GetGroupForm
     * @return array
     */
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
        return array('title' => $title, 'forumId' => $matches[1]);
    }

    /**
     * @depends testNew_SubmitGroupFormWithTitle
     * @param $args
     */
    public function testView($args)
    {
        $crawler = $this->client->request('GET', '/forum/' . $args['forumId']);
        $this->assertCount(1, $crawler->filter('title:contains("' . $args['title'] . '")'));

        return $args['forumId'];
    }

    /**
     * @depends testView
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
        $crawler = $this->client->request('GET', '/forum/' . $forumId . '/edit');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = self::$mediaIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $media = json_decode($crawler->filter('#__testMedia')->text(), true);

        $this->assertTrue(is_array($media));
        $this->assertCount(count(self::$mediaIds), $media);
        foreach ($media as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }

        return $forumId;
    }

    /**
     * @depends testEdit_SubmitFormWithMedia
     * @param $forumId
     */
    public function testDelete($forumId)
    {
        $this->client->request('POST', '/forum/' . $forumId . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasDeleted', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertTrue($response['wasDeleted']);
        $this->assertRegExp('/\/forum\/$/', $response['redirectUrl']);
    }
}
