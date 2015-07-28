<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MyFilesControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MyFilesControllerTest extends BaseWebTestCase
{
    /**
     * @var User
     */
    private $loggedInUser;

    /**
     * @var string
     */
    private $filesPath;

    /**
     * @var string
     */
    private $resourceUploadPath;

    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));

        $this->client = static::createClient();
        /** @var User $user */
        $user = $this->referenceRepo->getReference('test_user_1');
        Common::login($this->client, $user->getUsername());
        $this->loggedInUser = $user;

        // give logged in user media
        for ($i = 1; $i <= 2; $i++) {
            /** @var Media $media */
            $media = $this->referenceRepo->getReference('test_media_1_' . $i);
            $media->setOwner($this->loggedInUser);
            $this->entityManager->persist($media);
        }
        $this->entityManager->flush();

        $this->filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $this->resourceUploadPath = implode('/', $this->getContainer()->getParameter('imdc_terptube.resource_file'));
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/myFiles/');
        $this->logResponse(__FUNCTION__);

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
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('page', $response);

        $crawler = new Crawler($response['page']);
        $this->assertCount(1, $crawler->filter(
            '.tt-myFiles-list-table, .tt-myFiles-grid-div'
        ), 'list-table or grid-div should be present');
        $this->assertCount(0, $crawler->filter('form[name="media_chooser"]'),
            'media_chooser form should not be present');
    }

    public function testAddRecording_Chrome()
    {
        $videoBlob = LoadTestMedia::createUploadedFile($this->filesPath, 'video.webm');
        $audioBlob = LoadTestMedia::createUploadedFile($this->filesPath, 'audio.wav');

        $this->client->request('POST', '/myFiles/add/recording', array(), array(
            'video-blob' => $videoBlob,
            'audio-blob' => $audioBlob
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertFalse($response['media']['is_interpretation']);
        $this->assertFileExists($this->resourceUploadPath . '/' . $response['media']['source_resource']['id'] . '.placeholder');
    }

    public function testAddRecording_Firefox()
    {
        $audioBlob = LoadTestMedia::createUploadedFile($this->filesPath, 'video_audio.webm');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true
        ), array(
            'audio-blob' => $audioBlob
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertFalse($response['media']['is_interpretation']);
        $this->assertFileExists($this->resourceUploadPath . '/' . $response['media']['source_resource']['id'] . '.placeholder');
    }

    public function testAddRecording_ChromeInterpretation()
    {
        $videoBlob = LoadTestMedia::createUploadedFile($this->filesPath, 'case02_video_1316.918333s_480p_1000k.webm');
        $audioBlob = LoadTestMedia::createUploadedFile($this->filesPath, 'case02_audio_1316.918333s.wav');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'video-blob' => $videoBlob,
            'audio-blob' => $audioBlob
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertTrue($response['media']['is_interpretation']);
        $this->assertFileExists($this->resourceUploadPath . '/' . $response['media']['source_resource']['id'] . '.placeholder');
    }

    public function testAddRecording_FirefoxInterpretation()
    {
        $audioBlob = LoadTestMedia::createUploadedFile($this->filesPath,
            'case02_video+audio_1316.918333s_480p_1000k.webm');

        $this->client->request('POST', '/myFiles/add/recording', array(
            'isFirefox' => true,
            'isInterpretation' => true,
            'sourceStartTime' => 1.00,
            'sourceId' => 4
        ), array(
            'audio-blob' => $audioBlob
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('responseCode', $response);
        $this->assertArrayHasKey('feedback', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertEquals(200, $response['responseCode']);
        $this->assertEquals('media added', $response['feedback']);
        $this->assertTrue($response['media']['is_interpretation']);
        $this->assertFileExists($this->resourceUploadPath . '/' . $response['media']['source_resource']['id'] . '.placeholder');
    }

    public function testAddAction()
    {
        $file = LoadTestMedia::createUploadedFile($this->filesPath, 'case02_video+audio_1316.918333s_480p_1000k.webm');

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
                'source_resource' => array(
                    'file' => $file))));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('wasUploaded', $response);
        $this->assertArrayHasKey('media', $response);
        $this->assertTrue($response['wasUploaded']);
        $this->assertFileExists($this->resourceUploadPath . '/' . $response['media']['source_resource']['id'] . '.placeholder');
    }
}
