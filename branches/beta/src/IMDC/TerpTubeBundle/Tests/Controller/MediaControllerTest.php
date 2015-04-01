<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MediaControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MediaControllerTest extends WebTestCase
{
    // index 2 must not be in use. index 3 must be in use
    private static $mediaIds = array(4, 1, 234, 349); // shuffle for order check

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);
    }

    public function testList_All()
    {
        $this->client->request('GET', '/media/');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertTrue(is_array($response['media']));
        $this->assertGreaterThanOrEqual(1, count($response['media']));
    }

    /**
     * @return array
     */
    public function testList_SpecificIds()
    {
        $this->client->request('GET', '/media/?id=' . implode(',', self::$mediaIds));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertTrue(is_array($response['media']));
        $this->assertCount(count(self::$mediaIds), $response['media']);
        // check order
        foreach (self::$mediaIds as $key => $mediaId) {
            $this->assertEquals($response['media'][$key]['id'], $mediaId);
        }

        return $response['media'][2];
    }

    /**
     * @depends testList_SpecificIds
     * @param $model
     * @return array
     */
    public function testEdit($model)
    {
        $model['title'] = 'test:edit:' . rand();

        $this->client->request('POST', '/media/' . $model['id'] . '/edit', array(
            'media' => json_encode($model)
        ));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals($model['id'], $response['media']['id']);
        $this->assertEquals($model['title'], $response['media']['title']);

        return $response['media'];
    }

    /**
     * @depends testEdit
     * @param $model
     * @return array
     */
    public function testTrim($model)
    {
        $this->client->request('POST', '/media/' . $model['id'] . '/trim', array(
            'startTime' => '0.4',
            'endTime' => '2.2'
        ));

        file_put_contents(
            '../MediaControllerTest.testTrim.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals($model['id'], $response['media']['id']);

        return $response['media'];
    }

    /**
     * @depends testTrim
     * @param $model
     */
    public function testDelete_NotInUseSuccess($model)
    {
        $this->client->request('POST', '/media/' . $model['id'] . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertEquals(200, $response['responseCode']);
    }

    public function testDelete_InUseFail()
    {
        $this->client->request('POST', '/media/' . self::$mediaIds[3] . '/delete');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('mediaInUse', $response);
        $this->assertEquals(400, $response['responseCode']);
        $this->assertTrue(is_array($response['mediaInUse']));
    }

    /**
     * @depends testDelete_InUseFail
     */
    public function testDelete_InUseSuccess()
    {
        $this->client->request('POST', '/media/' . self::$mediaIds[3] . '/delete', array(
            'confirm' => true
        ));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertEquals(200, $response['responseCode']);
    }
}
