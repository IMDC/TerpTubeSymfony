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
    private static $groupId = 4;
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
    public function testNew_SubmitFormWithMedia()
    {
        $crawler = $this->client->request('GET', '/forum/new');

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = self::$mediaIds;
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

        // manually delete
        $this->delete($model);
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

        // manually delete
        $this->delete(Common::getModel($crawler));
    }

    public function testNew_GetGroupForm()
    {
        $crawler = $this->client->request('GET', '/forum/new/' . self::$groupId);

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
        $title = 'test:new';
        $crawler = $this->client->request('GET', '/forum/new/' . self::$groupId);

        $form = $crawler->filter('form[name="forum"] > button:contains("Create")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $model = Common::getModel($crawler);

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));
        $this->assertEquals(self::$groupId, $model['group']['id']);

        return $model;
    }

    /**
     * @depends testNew_SubmitGroupFormWithTitle
     * @param $model
     * @return array
     */
    public function testView($model)
    {
        $crawler = $this->client->request('GET', '/forum/' . $model['id']);
        $this->assertCount(1, $crawler->filter('title:contains("' . $model['title_text'] . '")'));

        return $model;
    }

    /**
     * @depends testView
     * @param $model
     * @return array
     */
    public function testEdit_GetForm($model)
    {
        $crawler = $this->client->request('GET', '/forum/' . $model['id'] . '/edit');
        $this->assertCount(1, $crawler->filter('form[name="forum"]'), 'a single forum form should be present');

        return $model;
    }

    /**
     * @depends testEdit_GetForm
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithTitle($model)
    {
        $title = 'test:edit';
        $crawler = $this->client->request('GET', '/forum/' . $model['id'] . '/edit');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form(array(
            'forum[titleText]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));

        return $model;
    }

    /**
     * @depends testEdit_SubmitFormWithTitle
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithMedia($model)
    {
        $crawler = $this->client->request('GET', '/forum/' . $model['id'] . '/edit');

        $form = $crawler->filter('form[name="forum"] > div > div > button:contains("Save")')->form();
        $values = $form->getPhpValues();
        $values['forum']['titleMedia'] = self::$mediaIds;
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
    public function testDelete($model)
    {
        $this->client->request('POST', '/forum/' . $model['id'] . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasDeleted', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertTrue($response['wasDeleted']);
        $this->assertRegExp('/\/forum\/$/', $response['redirectUrl']);
    }

    private function delete($model)
    {
        // manually delete the forum
        $em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $forum = $em->getRepository('IMDCTerpTubeBundle:Forum')->find($model['id']);
        $em->remove($forum);
        $em->flush();
    }
}
