<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ContactControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ContactControllerTest extends WebTestCase
{
    private static $userIds = array(13, 14);

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);

        // add users to friends list
        foreach (self::$userIds as $userId) {
            $this->client->request('GET', '/member/friends/' . $userId . '/add');
        }
    }
    
    public function testList()
    {
        // list style
        $crawler = $this->client->request('GET', '/contacts/?style=list');
        $tableCount = $crawler->filter('.tab-pane[id^=tab]');

        // grid style
        $crawler = $this->client->request('GET', '/contacts/?style=grid');
        $gridCount = $crawler->filter('.tab-pane[id^=tab]');

        // four tables/divs (all, mentors, mentees, friends)
        $this->assertCount(4, $tableCount);
        $this->assertCount(4, $gridCount);
    }

    public function testDelete_Fail()
    {
        $this->client->request('POST', '/contacts/remove', array(
            'userIds' => self::$userIds,
            'contactList' => 'error'
        ));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertFalse($response['success']);
    }

    public function testDelete_Success()
    {
        $this->client->request('POST', '/contacts/remove', array(
            'userIds' => self::$userIds,
            'contactList' => 'friends'
        ));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
    }
}
