<?php

namespace IMDC\TerpTubeBundle\Command;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Consumer\AbstractMediaConsumer;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class ResourcePopulateMetaDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('imdc:resource:populate-metadata')
            ->setDescription('Truncates then populates the \'meta_data\' table for \'resource_file\'s');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $output->writeln('Clearing meta_data in resource_file table');
        $em->getConnection()->exec("
            SET FOREIGN_KEY_CHECKS = 0;
            UPDATE resource_file SET meta_data = NULL");

        $output->writeln('Truncating meta_data table');
        $em->getConnection()->exec("
            TRUNCATE meta_data;
            SET FOREIGN_KEY_CHECKS = 1;");

        $mediaArray = $em->getRepository('IMDCTerpTubeBundle:Media')->findAll();

        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');

        /** @var Media $media */
        foreach ($mediaArray as $media) {
            $output->writeln(sprintf('media_id=%d', $media->getId()));

            $mediaType = $media->getType();

            /** @var ResourceFile $resource */
            if ($media->getSourceResource() != null) {
                $output->writeln(sprintf('source_resource_id=%d', $media->getSourceResource()->getId()));
                AbstractMediaConsumer::_updateMetaData($mediaType, $media->getSourceResource(), $transcoder);
            }

            foreach ($media->getResources() as $resource) {
                $output->writeln(sprintf('resource_id=%d', $resource->getId()));
                AbstractMediaConsumer::_updateMetaData($mediaType, $resource, $transcoder);
            }

            $em->persist($media);
        }

        $em->flush();
    }
}
