<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PostControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class PostControllerTest extends WebTestCase
{
    private static $threadId = 17;
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

    public function testNew_GetForm()
    {
        $this->client->request('GET', '/post/new/' . self::$threadId);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasReplied', $response);
        $this->assertArrayHasKey('html', $response);
        $this->assertFalse($response['wasReplied']);

        $crawler = new Crawler($response['html']);
        $this->assertCount(1, $crawler->filter('form[name="post"]'), 'a single post form should be present');
    }

    /**
     * @return array
     */
    public function testNew_SubmitFormWithMediaAndContent()
    {
        $content = 'test:new';
        $this->client->request('GET', '/post/new/' . self::$threadId);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $crawler = new Crawler($response['html'], $this->client->getRequest()->getUri());

        $form = $crawler->filter('form[name="post"] > button:contains("Reply")')->form();
        $values = $form->getPhpValues();
        $values['post']['attachedFile'] = self::$mediaIds;
        $values['post']['content'] = $content;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasReplied', $response);
        $this->assertArrayHasKey('post', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertTrue($response['wasReplied']);

        $model = $response['post'];
        $this->assertNotNull($model['id']);
        // check existence
        foreach ($model['ordered_media'] as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }
        // check order
        foreach (self::$mediaIds as $key => $mediaId) {
            $this->assertEquals($model['ordered_media'][$key]['id'], $mediaId);
        }

        $this->assertRegExp('/.*\\/' . self::$threadId . '.*/', $response['redirectUrl']);

        return $model;
    }

    /**
     * @depends testNew_SubmitFormWithMediaAndContent
     * @param $model
     * @return array
     */
    public function testView($model)
    {
        $this->client->request('GET', '/post/' . $model['id']);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('html', $response);

        $crawler = new Crawler($response['html']);
        $this->assertCount(1, $crawler->filter('p:contains("' . $model['content'] . '")'));

        return $model;
    }

    /**
     * @depends testView
     * @param $model
     * @return array
     */
    public function testEdit_GetForm($model)
    {
        $this->client->request('GET', '/post/' . $model['id'] . '/edit');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasEdited', $response);
        $this->assertArrayHasKey('html', $response);
        $this->assertFalse($response['wasEdited']);

        $crawler = new Crawler($response['html']);
        $this->assertCount(1, $crawler->filter('form[name="post"]'), 'a single post form should be present');

        return $model;
    }

    /**
     * @depends testEdit_GetForm
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithMediaAndContent($model)
    {
        $content = 'test:edit';
        $this->client->request('GET', '/post/' . $model['id'] . '/edit');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $crawler = new Crawler($response['html'], $this->client->getRequest()->getUri());

        $form = $crawler->filter('form[name="post"] > button:contains("Edit")')->form();
        $values = $form->getPhpValues();
        $values['post']['attachedFile'] = self::$mediaIds;
        $values['post']['content'] = $content;
        $this->client->request($form->getMethod(), $form->getUri(), $values);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasEdited', $response);
        $this->assertArrayHasKey('post', $response);
        $this->assertArrayHasKey('html', $response);
        $this->assertTrue($response['wasEdited']);

        $model = $response['post'];
        $this->assertNotNull($model['id']);
        // check existence
        foreach ($model['ordered_media'] as $m) {
            $this->assertContains($m['id'], self::$mediaIds);
        }
        // check order
        foreach (self::$mediaIds as $key => $mediaId) {
            $this->assertEquals($model['ordered_media'][$key]['id'], $mediaId);
        }

        $crawler = new Crawler($response['html']);
        $this->assertCount(1, $crawler->filter('p:contains("' . $model['content'] . '")'));

        return $model;
    }

    /**
     * @depends testEdit_SubmitFormWithMediaAndContent
     * @param $model
     * @return array
     */
    public function testDelete($model)
    {
        $this->client->request('POST', '/post/' . $model['id'] . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasDeleted', $response);
        $this->assertTrue($response['wasDeleted']);
    }
}
