<?php

namespace IMDC\TerpTubeBundle\Command;

use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Thread;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Util\ClassUtils;

class GiveOwnershipCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('imdc:acl:give-ownership')
            ->setDescription('Give ownership to an object, by inserting ACL entries')
            ->addArgument('object_identifier', InputArgument::REQUIRED, 'The object identifier to give ownership to')
            ->addArgument('object_repo', InputArgument::REQUIRED, 'The object repository class name')
            ->addOption('user_id', null, InputOption::VALUE_REQUIRED, 'If set, this user will be granted ownership. Otherwise the object creator is used');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $object_identifier = $input->getArgument('object_identifier');
        $object_repo = $input->getArgument('object_repo');
        $user_id = $input->getOption('user_id');

        $object = $em->getRepository($object_repo)->find($object_identifier);
        if (!$object) {
            throw new \Exception('object not found');
        }

        $user = null;
        if ($user_id) {
            $user = $em->getRepository('IMDCTerpTubeBundle:User')->find($user_id);
        }
        if (!$user) {
            if ($object instanceof Forum || $object instanceof Thread) {
                $user = $object->getCreator();
            } else if ($object instanceof UserGroup) {
                $user = $object->getUserFounder();
            } else {
                throw new \Exception('user not found');
            }
        }

        $aclProvider = $this->getContainer()->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $acl = null;
        try {
            $acl = $aclProvider->findAcl($objectIdentity);
        } catch (AclAlreadyExistsException $ex) {
            $acl = $aclProvider->createAcl($objectIdentity);
        }
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);

        $output->writeln(sprintf(
            '%s now has ownership access to identifier %d of class %s',
            $user->getUsername(),
            $object_identifier,
            ClassUtils::getRealClass($object)
        ));
    }
}
