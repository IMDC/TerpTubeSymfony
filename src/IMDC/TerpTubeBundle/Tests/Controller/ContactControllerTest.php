<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Rest\Exception\ContactException;
use IMDC\TerpTubeBundle\Rest\RestResponse;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Tests\Common;

/**
 * Class ContactControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class ContactControllerWebTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestUsers'
        ));

        $this->client = static::createClient();
        /** @var User $user1 */
        $user1 = $this->referenceRepo->getReference('test_user_1');

        Common::login($this->client, $user1->getUsername());

        // add users to friends list
        $user2 = $this->referenceRepo->getReference('test_user_2');
        $user3 = $this->referenceRepo->getReference('test_user_3');

        $user1->addFriendsList($user2);
        $user1->addFriendsList($user3);

        $this->entityManager->persist($user1);
        $this->entityManager->flush();
    }

    public function testList()
    {
        // list style
        $crawler = $this->client->request('GET', '/contacts/?style=list');
        $this->logResponse(__FUNCTION__, 'list');
        $tableCount = $crawler->filter('.tab-pane[id^=tab]');

        // grid style
        $crawler = $this->client->request('GET', '/contacts/?style=grid');
        $this->logResponse(__FUNCTION__, 'grid');
        $gridCount = $crawler->filter('.tab-pane[id^=tab]');

        // four tables/divs (all, mentors, mentees, friends)
        $this->assertCount(4, $tableCount);
        $this->assertCount(4, $gridCount);
    }

    public function testDelete_Fail()
    {
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');

        $this->client->request('DELETE', '/api/v1/contact', array(
            'userIds' => array($user2->getId(), $user3->getId()),
            'contactList' => 'error'
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(ContactException::getCode(ContactException::BAD_REQUEST), $response['code']);
    }

    public function testDelete_Success()
    {
        /** @var User $user2 */
        $user2 = $this->referenceRepo->getReference('test_user_2');
        /** @var User $user3 */
        $user3 = $this->referenceRepo->getReference('test_user_3');

        $this->client->request('DELETE', '/api/v1/contact', array(
            'userIds' => array($user2->getId(), $user3->getId()),
            'contactList' => 'friends'
        ));
        $this->logResponse(__FUNCTION__);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals(RestResponse::OK, $response['code']);
    }
}
