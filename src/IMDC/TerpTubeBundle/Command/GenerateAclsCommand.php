<?php

namespace IMDC\TerpTubeBundle\Command;

use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class GenerateAclsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('imdc:acl:generate-acls')
            ->setDescription('Generate ACLs and relevant ACEs for supported Entities based on its access type and owner')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'If set, the ACE table will be truncated first');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $doFlush = $input->hasOption('flush');
        if ($doFlush) {
            $output->writeln('Truncating ACE table');
            $em->getConnection()->exec("TRUNCATE acl_entries");
        }

        $accessProvider = $this->getContainer()->get('imdc_terptube.security.acl.access_provider');
        $insertAces = function ($object, $user) use ($accessProvider) {
            $objectIdentity = AccessObjectIdentity::fromAccessObject($object);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $access = $accessProvider->createAccess($objectIdentity);
            $access->insertEntries($securityIdentity);
            $accessProvider->updateAccess($access);
        };

        $output->writeln('Inserting forum ACEs');
        $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->findAll();
        foreach ($forums as $forum) {
            if ($forum->getAccessType()->getId() !== AccessType::TYPE_GROUP) {
                $forum->setGroup(null);
                $em->flush();
            }

            $output->writeln(sprintf('id: %d, title: %s, owner: %s', $forum->getId(), $forum->getTitleText(), $forum->getCreator()->getUsername()));
            $insertAces($forum, $forum->getCreator());
        }

        $output->writeln('Inserting thread ACEs');
        $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->findAll();
        foreach ($threads as $thread) {
            $output->writeln(sprintf('id: %d, title: %s, owner: %s', $thread->getId(), $thread->getTitle(), $thread->getCreator()->getUsername()));
            $insertAces($thread, $thread->getCreator());
        }

        $aclProvider = $this->getContainer()->get('security.acl.provider');
        $output->writeln('Inserting group ACEs');
        $groups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findAll();
        foreach ($groups as $group) {
            $output->writeln(sprintf('id: %d, name: %s, owner: %s', $group->getId(), $group->getName(), $group->getUserFounder()->getUsername()));
            //$insertAces($group, $group->getUserFounder());

            $objectIdentity = ObjectIdentity::fromDomainObject($group);
            $securityIdentity = UserSecurityIdentity::fromAccount($group->getUserFounder());

            $acl = null;
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch (AclNotFoundException $ex) {
                $acl = $aclProvider->createAcl($objectIdentity);
            }
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
        }
    }
}
