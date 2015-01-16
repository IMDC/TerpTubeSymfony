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
        $crawler = $this->client->request('GET', '/contacts');

        // four tables (all, mentors, mentees, friends)
        $this->assertCount(4, $crawler->filter('table.tt-list-table th:nth-child(2)'));
    }
}
