<?php
namespace IMDC\TerpTubeBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\Thread;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use Symfony\Component\Process\Process;

class FixThreadTypesCommand extends ContainerAwareCommand
{

    protected function configure ()
    {
        $this->setName('imdc:thread:fix-types')->setDescription('Fixes the wrongly set types for the threads.');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()
            ->get('doctrine')
            ->getEntityManager();
        
        $threads = $em->getRepository('IMDCTerpTubeBundle:Thread')->findAll();
        
        foreach ($threads as $thread)
        {
            if (count($thread->getMediaIncluded()) > 0)
            {
                $ordered = $thread->getOrderedMedia();
                if ($thread->getType() != $ordered[0]->getType())
                {
                    $output->writeln(
                            "updating thread:" . $thread->getId() . ", from type: " . $thread->getType() . " to: " .
                                     $ordered[0]->getType());
                    $thread->setType($ordered[0]->getType()); // thread type is determined by the first associated media
                }
            }
            else
            {
                if (! is_null($thread->getType()))
                {
                    $thread->setType(NULL);
                    $output->writeln("updating thread:" . $thread->getId() . ", from type: " . $thread->getType() . " to: NULL");
                }
            }
            
            $em->flush();
        }
    }
}
