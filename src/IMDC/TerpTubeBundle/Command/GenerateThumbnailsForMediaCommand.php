<?php
namespace IMDC\TerpTubeBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class GenerateThumbnailsForMediaCommand extends ContainerAwareCommand
{

    protected function configure ()
    {
        $this->setName('imdc:media:generate-thumbnails')
            ->setDescription('Generate thumbnails for all media files of type image or video')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'If set, all the thumbnails will be recreated');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()
            ->get('doctrine')
            ->getEntityManager();
        
        $transcoder = $this->getContainer()->get('imdc_terptube.transcoder');
        
        $fs = new Filesystem();
        
        $doFlush = $input->getOption('flush');
        if ($doFlush)
        {
            $output->writeln('Thumbnails will be recreated');
        }
        $mediaElements = $em->getRepository('IMDCTerpTubeBundle:Media')->findAll();
        $count = 0;
        foreach ($mediaElements as $media)
        {
            if ($media->getType() == Media::TYPE_IMAGE || $media->getType() == Media::TYPE_VIDEO)
            {
                if ($doFlush)
                {
                    $output->writeln('Removing thumbnail for media id:' . $media->getId());
                    $media->removeThumbnail();
                }
                
                if ($media->getThumbnailPath() == NULL)
                {
                    $count ++;
                    try
                    {
                        $output->writeln('Generating thumbnail for media id:' . $media->getId());
                        $thumbnailTempFile = $transcoder->createThumbnail(
                                $media->getResource()
                                    ->getAbsolutePath(), $media->getType());
                        $thumbnailFile = $media->getThumbnailRootDir() . "/" . $media->getResource()->getId() . ".png";
                        $fs->rename($thumbnailTempFile, $thumbnailFile, true);
                        $media->setThumbnailPath($media->getResource()
                            ->getId() . ".png");
                    }
                    catch (IOException $e)
                    {
                        $output->writeln("ERROR: " . $e->getTraceAsString());
                    }
                    if ($count % 20 == 0)
                        $em->flush();
                }
            }
        }
        $em->flush();
    }
}
