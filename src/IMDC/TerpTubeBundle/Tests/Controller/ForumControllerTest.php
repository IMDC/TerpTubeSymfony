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
            'ForumForm[titleText]' => 'test:new'
        ));

        $values = $form->getPhpValues();
        $values['ForumForm']['titleMedia'] = array(1);

        //$this->client->submit($form);
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();

        //echo $this->client->getResponse()->getContent(); die;

        $this->assertCount(1, $crawler->filter('title:contains("test:new")'));
        $this->assertCount(1, $crawler->filter('body > div.container-fluid > div:nth-child(2) > div > div > div.col-lg-offset-1.col-lg-11 > div > div > div:nth-child(2) > div:nth-child(2) [src^="/uploads/media/"]'));
    }

    public function testEdit()
    {
        $crawler = $this->client->request('GET', '/forum/26/edit');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filter('button:contains("Save")')->eq(0)->form(array(
            'ForumForm[titleText]' => 'test:edit'
        ));

        $values = $form->getPhpValues();
        $values['ForumForm']['titleMedia'] = array(4);

        //$this->client->submit($form);
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();

        //echo $this->client->getResponse()->getContent(); die;

        $this->assertCount(1, $crawler->filter('title:contains("test:edit")'));
        $this->assertCount(2, $crawler->filter('body > div.container-fluid > div:nth-child(2) > div > div > div.col-lg-offset-1.col-lg-11 > div > div > div:nth-child(2) > div:nth-child(2) [src^="/uploads/media/"]'));
    }
}
