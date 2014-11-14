<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ForumControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => 'test',
            '_password'  => 'test'
        ));

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
    }

    public function testNew()
    {
        $crawler = $this->client->request('GET', '/forum/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filter('button:contains("Create")')->eq(0)->form(array(
            'ForumForm[mediatextarea]' => '1',
            'ForumForm[titleText]' => 'testNew'
        ));

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();

        //echo $this->client->getResponse()->getContent(); die;

        $this->assertGreaterThan(0, $crawler->filter('[src^="/uploads/media/1."]')->count());
        $this->assertCount(1, $crawler->filter('title:contains("testNew")'));
    }

    public function testEdit()
    {
        $crawler = $this->client->request('GET', '/forum/26/edit');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filter('button:contains("Save")')->eq(0)->form(array(
            'ForumForm[mediatextarea]' => '4',
            'ForumForm[titleText]' => 'testEdit'
        ));

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();

        //echo $this->client->getResponse()->getContent(); die;

        $this->assertGreaterThan(0, $crawler->filter('[src^="/uploads/media/4."]')->count());
        $this->assertCount(1, $crawler->filter('title:contains("testEdit")'));
    }
}
