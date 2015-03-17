<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MyFilesControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MyFilesControllerTest extends WebTestCase
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

    private static function prepareWebmData()
    {
        //TODO external and separate test data for video and audio
        copy('web/uploads/media/148.webm', 'audio.webm');
    }

    public function testAddRecording_Firefox()
    {
        MyFilesControllerTest::prepareWebmData();

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true
        ), array(
            'audio-blob' => new UploadedFile('audio.webm', 'audio.webm', null, filesize('audio.webm'), null, true)
        ));

        file_put_contents(
            'MyFilesControllerTest.testAddRecording_Firefox.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
    }

    public function testAddRecording_FirefoxInterpretation()
    {
        MyFilesControllerTest::prepareWebmData();

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true,
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'audio-blob' => new UploadedFile('audio.webm', 'audio.webm', null, filesize('audio.webm'), null, true)
        ));

        file_put_contents(
            'MyFilesControllerTest.testAddRecording_FirefoxInterpretation.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
    }
}
