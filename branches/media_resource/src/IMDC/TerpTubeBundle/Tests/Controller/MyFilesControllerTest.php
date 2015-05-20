<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Tests\Common;
use IMDC\TerpTubeBundle\Tests\MediaTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

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

    public function testList()
    {
        $crawler = $this->client->request('GET', '/myFiles/');

        $this->assertCount(1, $crawler->filter(
            '.tt-myFiles-list-table, .tt-myFiles-grid-div'
        ), 'list-table or grid-div should be present');
        $this->assertCount(1, $crawler->filter('form[name="media_chooser"]'),
            'a single media_chooser form should be present');
        $this->assertCount(0, $crawler->filter('a.mediachooser-select'),
            'media_chooser \'select file button\' should not be present');
    }

    public function testList_Ajax()
    {
        $this->client->request('GET', '/myFiles/', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ));

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('page', $response);

        $crawler = new Crawler();
        $crawler->addContent($response['page']);

        $this->assertCount(1, $crawler->filter(
            '.tt-myFiles-list-table, .tt-myFiles-grid-div'
        ), 'list-table or grid-div should be present');
        $this->assertCount(0, $crawler->filter('form[name="media_chooser"]'),
            'media_chooser form should not be present');
    }

    public function testAddRecording_Chrome()
    {
        $videoBlob = $this->createUploadedFile('case02_video_1316.918333s_480p_1000k.webm');
        $audioBlob = $this->createUploadedFile('case02_audio_1316.918333s.wav');

        $this->client->request('POST', '/myFiles/add/recording', array(), array(
            'video-blob' => $videoBlob,
            'audio-blob' => $audioBlob
        ));

        file_put_contents(
            '../test_logs/MyFilesControllerTest.testAddRecording_Chrome.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertFalse($response['media']['is_interpretation']);
        $this->assertFileExists(
            static::$kernel->getRootDir() . '/../web/uploads/media/' . $response['media']['resource']['id'] . '.webm');

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }

    public function testAddRecording_Firefox()
    {
        $audioBlob = $this->createUploadedFile('case02_video+audio_1316.918333s_480p_1000k.webm');

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
        $this->assertFalse($response['media']['is_interpretation']);
        $this->assertFileExists(
            static::$kernel->getRootDir() . '/../web/uploads/media/' . $response['media']['resource']['id'] . '.webm');

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }

    public function testAddRecording_ChromeInterpretation()
    {
        $videoBlob = $this->createUploadedFile('case02_video_1316.918333s_480p_1000k.webm');
        $audioBlob = $this->createUploadedFile('case02_audio_1316.918333s.wav');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'video-blob' => $videoBlob,
            'audio-blob' => $audioBlob
        ));

        file_put_contents(
            '../test_logs/MyFilesControllerTest.testAddRecording_ChromeInterpretation.html',
            $this->client->getResponse()->getContent()
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertTrue($response['media']['is_interpretation']);
        $this->assertFileExists(
            static::$kernel->getRootDir() . '/../web/uploads/media/' . $response['media']['resource']['id'] . '.webm');

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }

    public function testAddRecording_FirefoxInterpretation()
    {
        $audioBlob = $this->createUploadedFile('case02_video+audio_1316.918333s_480p_1000k.webm');

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
        $this->assertTrue($response['media']['is_interpretation']);
        $this->assertFileExists(
            static::$kernel->getRootDir() . '/../web/uploads/media/' . $response['media']['resource']['id'] . '.webm');

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }

    public function testAddAction()
    {
        $file = $this->createUploadedFile('case02_video+audio_1316.918333s_480p_1000k.webm');

        $form = $this->client->getContainer()
            ->get('form.factory')
            ->create(new MediaType());
        $formView = $form->createView();

        $values = array();
        Common::formViewToPhpValues($formView, $values);

        $token = $this->client->getContainer()
            ->get('form.csrf_provider')
            ->generateCsrfToken($form->getName());
        $values[$form->getName()]['_token'] = $token;

        $this->client->request('POST', '/myFiles/add', $values, array(
            'media' => array(
                'resource' => array(
                    'file' => $file))));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasUploaded', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertTrue($response['wasUploaded']);
        $this->assertFileExists(
            static::$kernel->getRootDir() . '/../web/uploads/media/' . $response['media']['resource']['id'] . '.webm');

        // find media to have it removed at tear down
        $this->media = $this->entityManager->find('IMDCTerpTubeBundle:Media', $response['media']['id']);
    }
}
