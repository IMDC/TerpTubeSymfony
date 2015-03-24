<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class MyFilesControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MyFilesControllerTest extends MediaTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();

        Common::login($this->client);
    }

    public function testAddRecording_Firefox()
    {
        $audioBlob = $this->createUploadedFile('video_audio.webm');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true
        ), array(
            'audio-blob' => $audioBlob
        ));

        file_put_contents(
            '../test_logs/MyFilesControllerTest.testAddRecording_Firefox.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }

    public function testAddRecording_FirefoxInterpretation()
    {
        $audioBlob = $this->createUploadedFile('video_audio.webm');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true,
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'audio-blob' => $audioBlob
        ));

        file_put_contents(
            '../test_logs/MyFilesControllerTest.testAddRecording_FirefoxInterpretation.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }
}
