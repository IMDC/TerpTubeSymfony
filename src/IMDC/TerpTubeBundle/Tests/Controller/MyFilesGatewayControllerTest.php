<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MyFilesGatewayControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MyFilesGatewayControllerTest extends WebTestCase
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

    public function testAddRecordingFirefox()
    {
        MyFilesGatewayControllerTest::prepareWebmData();

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true
        ), array(
            'audio-blob' => new UploadedFile('audio.webm', 'audio.webm', null, filesize('audio.webm'), null, true)
        ));

        file_put_contents(
            'MyFilesGatewayControllerTest_testAddRecordingFirefox.html',
            $this->client->getResponse()->getContent()
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
    }

    public function testAddRecordingFirefoxInterpretation()
    {
        MyFilesGatewayControllerTest::prepareWebmData();

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true,
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'audio-blob' => new UploadedFile('audio.webm', 'audio.webm', null, filesize('audio.webm'), null, true)
        ));

        file_put_contents(
            'MyFilesGatewayControllerTest_testAddRecordingFirefoxInterpretation.html',
            $this->client->getResponse()->getContent()
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
    }
}
