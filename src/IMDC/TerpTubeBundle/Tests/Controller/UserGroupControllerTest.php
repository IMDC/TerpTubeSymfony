<?php

namespace IMDC\TerpTubeBundle\Tests\Controller;

use IMDC\TerpTubeBundle\Tests\Common;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserGroupControllerTest
 * @package IMDC\TerpTubeBundle\Tests\Controller
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UserGroupControllerTest extends WebTestCase
{
    private static $groupId = 4;
    private static $userIds = array(13, 14);

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        $this->client = static::createClient();

        Common::login($this->client);

        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->entityManager = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // remove users from group
        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find(self::$groupId);
        $qb = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->createQueryBuilder('u');
        $users = $qb->where($qb->expr()->in('u.id', self::$userIds))->getQuery()->getResult();
        foreach ($users as $user) {
            $user->removeUserGroup($group);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();

        // add users to friends list to bypass invitation step
        foreach (self::$userIds as $userId) {
            $this->client->request('GET', '/member/friends/' . $userId . '/add');
        }
    }

    public function testManage_GetForms()
    {
        // list style
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage?style=list');
        $tableCount = $crawler->filter('.tab-pane[id^=tab]');

        // grid style
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage?style=grid');
        $gridCount = $crawler->filter('.tab-pane[id^=tab]');

        // two tables/divs (group members, community)
        $this->assertCount(2, $tableCount);
        $this->assertCount(2, $gridCount);
        $this->assertCount(1, $crawler->filter('form[name="user_group_manage_search"]'),
            'a single "user_group_manage_search" form should be present');
        $this->assertCount(2, $crawler->filter('form[name="user_group_manage_remove"], form[name="user_group_manage_add"]'),
            '"users_select" forms should be present');
    }

    public function testManage_SearchCommunity()
    {
        $user = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->find(self::$userIds[0]);
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage?style=list');

        $form = $crawler->filter('form[name="user_group_manage_search"]')->form(array(
            'user_group_manage_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('.tab-pane[id^=tab] [data-uid="' . $user->getId() . '"]'),
            'user should be present'
        );
    }

    public function testManage_AddMembers()
    {
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage');

        $form = $crawler->filter('form[name="user_group_manage_add"]')->form();
        $values = $form->getPhpValues();
        $values['user_group_manage_add']['users'] = self::$userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find(self::$groupId);
        $qb = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->createQueryBuilder('u');
        $users = $qb->where($qb->expr()->in('u.id', self::$userIds))->getQuery()->getResult();
        foreach ($users as $user) {
            $this->assertTrue($group->getMembers()->contains($user), 'group should contain user as member');
        }
    }

    public function testManage_SearchMembers()
    {
        $user = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->find(self::$userIds[1]);
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage?style=list');

        $form = $crawler->filter('form[name="user_group_manage_search"]')->form(array(
            'user_group_manage_search[username]' => $user->getUsername()
        ));
        $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('.tab-pane[id^=tab] [data-uid="' . $user->getId() . '"]'),
            'user should be present'
        );
    }

    public function testManage_RemoveMembers()
    {
        $crawler = $this->client->request('GET', '/group/' . self::$groupId . '/manage');

        $form = $crawler->filter('form[name="user_group_manage_remove"]')->form();
        $values = $form->getPhpValues();
        $values['user_group_manage_add']['users'] = self::$userIds;
        $this->client->request($form->getMethod(), $form->getUri(), $values);

        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find(self::$groupId);
        $qb = $this->entityManager->getRepository('IMDCTerpTubeBundle:User')->createQueryBuilder('u');
        $users = $qb->where($qb->expr()->in('u.id', self::$userIds))->getQuery()->getResult();
        foreach ($users as $user) {
            $this->assertFalse($group->getMembers()->contains($user), 'group should not contain user as member');
        }
    }
}
