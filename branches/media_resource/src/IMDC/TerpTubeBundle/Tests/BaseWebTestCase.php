<?php

namespace IMDC\TerpTubeBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class BaseWebTestCase
 * @package IMDC\TerpTubeBundle\Tests
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class BaseWebTestCase extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepo;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    private $logsPath;

    /**
     * @param array $fixtures
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function reloadDatabase(array $fixtures)
    {
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
        if (!isset($metadatas)) {
            $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        }
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }

        // always load default (prod) fixtures
        array_unshift($fixtures,
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadAccessTypes',
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadInvitationTypes');

        /** @var ORMExecutor $executor */
        $executor = $this->loadFixtures($fixtures);
        $this->referenceRepo = $executor->getReferenceRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function postFixtureSetup()
    {
        // flush acls and aces. see IMDC\TerpTubeBundle\Command\GenerateAclsCommand::execute
        $this->entityManager->getConnection()->exec("
            SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE acl_classes;
            TRUNCATE acl_entries;
            TRUNCATE acl_object_identities;
            TRUNCATE acl_object_identity_ancestors;
            TRUNCATE acl_security_identities;
            SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * @param $object
     * @param $user
     * @param null $mask
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    protected function grantAccessToObject($object, $user, $mask = null)
    {
        if ($object instanceof UserGroup) {
            if (empty($mask))
                throw new \InvalidArgumentException('$mask must be specified for UserGroup objects.');

            $aclProvider = $this->getContainer()->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($object);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $acl = $aclProvider->createAcl($objectIdentity);
            $acl->insertObjectAce($securityIdentity, $mask);
            $aclProvider->updateAcl($acl);
        } else {
            /* @var $accessProvider AccessProvider */
            $accessProvider = $this->getContainer()->get('imdc_terptube.security.acl.access_provider');
            $objectIdentity = AccessObjectIdentity::fromAccessObject($object);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity);
            $accessProvider->setSecurityIdentities($objectIdentity, $object);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess();
        }
    }

    /**
     * @param $function
     * @param string $suffix
     * @param null $class
     */
    protected function logResponse($function, $suffix = '', $class = null)
    {
        $class = $class ?: get_class($this);

        if (!$this->logsPath)
            $this->logsPath = $this->getContainer()->getParameter('imdc_terptube.tests.logs_path');

        file_put_contents(
            $this->logsPath . substr($class, strrpos($class, '\\')) . '.' . $function . (!empty($suffix) ? '_' . $suffix : '') . '.html',
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @param $media
     * @return array
     */
    protected static function getShuffledMediaIds($media)
    {
        $mediaIds = array();

        /** @var Media $m */
        foreach ($media as $m)
            $mediaIds[] = $m->getId();

        shuffle($mediaIds);

        return $mediaIds;
    }

    /**
     * @param $media
     * @param $mediaIds
     */
    protected function assertMedia($media, $mediaIds)
    {
        $this->assertTrue(is_array($media));
        $this->assertCount(count($mediaIds), $media);
        // check existence
        foreach ($media as $m) {
            $this->assertContains($m['id'], $mediaIds);
        }
        // check order
        foreach ($mediaIds as $key => $mediaId) {
            $this->assertEquals($media[$key]['id'], $mediaId);
        }
    }
}
