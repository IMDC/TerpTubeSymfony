<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ThreadControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ThreadControllerTest extends WebTestCase
{
    private static $forumId = 145;
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

    public function testNew_GetFormWithMedia()
    {
        $crawler = $this->client->request('GET', '/thread/new/myFiles/' . self::$mediaIds[0]);

        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');
        $this->assertCount(1, $crawler->filter('[name^="thread[forum]"]'), 'forum field should be present');
        $this->assertCount(1, $crawler->filter('[name^="thread[mediaIncluded]"][value="' . self::$mediaIds[0] . '"]'),
            'media should be present');
        $this->assertCount(1, $crawler->filter('#thread_accessType_0:checked'),
            '"public" access type should be checked');
    }

    public function testNew_GetForm()
    {
        $crawler = $this->client->request('GET', '/thread/new/' . self::$forumId);

        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');
        $this->assertCount(1, $crawler->filter('#thread_accessType_0:checked'),
            '"public" access type should be checked');
    }

    public function testNew_SubmitFormWithMedia()
    {
        $crawler = $this->client->request('GET', '/thread/new/' . self::$forumId);

        $form = $crawler->filter('form[name="thread"] > button:contains("Create")')->form();
        $values = $form->getPhpValues();
        $values['thread']['mediaIncluded'] = self::$mediaIds;
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
        $this->delete($crawler);
    }

    /**
     * @return array
     */
    public function testNew_SubmitFormWithTitle()
    {
        $title = 'test:new:' . rand();
        $crawler = $this->client->request('GET', '/thread/new/' . self::$forumId);

        $form = $crawler->filter('form[name="thread"] > button:contains("Create")')->form(array(
            'thread[title]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));

        return Common::getModel($crawler);
    }

    /**
     * @depends testNew_SubmitFormWithTitle
     * @param $model
     * @return array
     */
    public function testView($model)
    {
        $crawler = $this->client->request('GET', '/thread/' . $model['id']);
        $this->assertCount(1, $crawler->filter('title:contains("' . $model['title'] . '")'));

        return $model;
    }

    /**
     * @depends testView
     * @param $model
     * @return array
     */
    public function testEdit_GetForm($model)
    {
        $crawler = $this->client->request('GET', '/thread/' . $model['id'] . '/edit');
        $this->assertCount(1, $crawler->filter('form[name="thread"]'), 'a single thread form should be present');

        return $model;
    }

    /**
     * @depends testEdit_GetForm
     * @param $model
     * @return array
     */
    public function testEdit_SubmitFormWithTitle($model)
    {
        $title = 'test:edit:' . rand();
        $crawler = $this->client->request('GET', '/thread/' . $model['id'] . '/edit');

        $form = $crawler->filter('form[name="thread"] > div > div > button:contains("Save")')->form(array(
            'thread[title]' => $title
        ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('title:contains("' . $title . '")'));

        return Common::getModel($crawler);
    }

    /**
     * @depends testEdit_SubmitFormWithTitle
     * @param $model
     * @return array
     */
    public function testDelete($model)
    {
        $this->client->request('POST', '/thread/' . $model['id'] . '/delete');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('wasDeleted', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertTrue($response['wasDeleted']);
        $this->assertRegExp('/\/forum\/\d+$/', $response['redirectUrl']);
    }

    private function delete($crawler)
    {
        // manually delete the thread
        $model = Common::getModel($crawler);
        $em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $thread = $em->getRepository('IMDCTerpTubeBundle:Thread')->find($model['id']);
        $em->remove($thread);
        $em->flush();
    }
}
