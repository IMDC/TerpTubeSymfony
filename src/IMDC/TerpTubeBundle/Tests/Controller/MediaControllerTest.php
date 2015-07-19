<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MetaData;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Rest\Exception\MediaException;
use IMDC\TerpTubeBundle\Rest\MediaResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;
use JMS\Serializer\Serializer;

/**
 * Class MediaControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MediaControllerTest extends BaseWebTestCase
{
    /**
     * @var User
     */
    private $loggedInUser;

    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestForums',
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
            $media = $this->referenceRepo->getReference('test_media_' . $i);
            $media->setOwner($this->loggedInUser);
            $this->entityManager->persist($media);
        }
        $this->entityManager->flush();
    }

    public function testList_All()
    {
        $this->client->request('GET', '/api/v1/media');
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertTrue(is_array($response['media']));
        $this->assertGreaterThanOrEqual(1, count($response['media']));
    }

    public function testList_SpecificIds()
    {
        $mediaIds = BaseWebTestCase::getShuffledMediaIds($this->loggedInUser->getResourceFiles());

        $this->client->request('GET', '/api/v1/media?id=' . implode(',', $mediaIds));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertTrue(is_array($response['media']));
        $this->assertCount(count($mediaIds), $response['media']);
        $this->assertMedia($response['media'], $mediaIds);
    }

    public function testEdit()
    {
        /** @var Media $media */
        $media = $this->loggedInUser->getResourceFiles()->get(0);
        $media->setTitle('test:edit:' . rand());
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $this->client->request('PUT', '/api/v1/media/' . $media->getId() . '/edit', array(
            'media' => $serializer->serialize($media, 'json')
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertEquals($media->getId(), $response['media']['id']);
        $this->assertEquals($media->getTitle(), $response['media']['title']);
    }

    public function testTrim()
    {
        /** @var Media $media */
        $media = $this->loggedInUser->getResourceFiles()->get(0);
        $resource = new ResourceFile();
        $metaData = new MetaData();
        $metaData->setDuration(100);
        $resource->setMetaData($metaData);
        $media->addResource($resource);
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        $this->client->request('PATCH', '/api/v1/media/' . $media->getId() . '/trim', array(
            'startTime' => '0.4',
            'endTime' => '2.2'
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('media', $response);
        $this->assertEquals($media->getId(), $response['media']['id']);
    }

    public function testDelete_NotInUseSuccess()
    {
        /** @var Media $media */
        $media = $this->loggedInUser->getResourceFiles()->get(0);

        $this->client->request('DELETE', '/api/v1/media/' . $media->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(MediaResponse::OK, $response['code']);
    }

    public function testDelete_InUseFail()
    {
        /** @var Media $media */
        $media = $this->loggedInUser->getResourceFiles()->get(0);
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $forum->addTitleMedia($media);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/media/' . $media->getId());
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(MediaException::getCode(), $response['code']);
        $this->assertArrayHasKey('in_use', $response);
    }

    public function testDelete_InUseSuccess()
    {
        /** @var Media $media */
        $media = $this->loggedInUser->getResourceFiles()->get(0);
        /** @var Forum $forum */
        $forum = $this->referenceRepo->getReference('test_forum_1');
        $forum->addTitleMedia($media);
        $this->entityManager->persist($forum);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/v1/media/' . $media->getId(), array(
            'confirm' => true
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(MediaResponse::OK, $response['code']);
    }
}
