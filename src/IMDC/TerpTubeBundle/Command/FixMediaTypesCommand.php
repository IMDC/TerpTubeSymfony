<?php

namespace IMDC\TerpTubeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use IMDC\TerpTubeBundle\Event\UploadEvent;
use Symfony\Component\Process\Process;

class FixMediaTypesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName ( 'imdc:media:fix-types' )->setDescription ( 'Fixes the wrongly added videos as image types' );
	}
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->getContainer ()->get ( 'doctrine' )->getEntityManager ();
		
		$mediaElements = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->findAll ();
		$count = 0;
		foreach ( $mediaElements as $media )
		{
			if ($media->getType () == Media::TYPE_IMAGE || $media->getType () == Media::TYPE_OTHER)
			{
				$resource = $media->getResource ();
				$resourcePath = $resource->getAbsolutePath ();
				
				// Get mimetype
				$finfo = finfo_open ( FILEINFO_MIME_TYPE );
				if ($finfo)
				{
					$mimeType = finfo_file ( $finfo, $resourcePath );
					finfo_close ( $finfo );
				}
				
				if ($mimeType == 'application/octet-stream')
				{
					$process = new Process('file --mime-type ' . escapeshellarg($resourcePath));
					$process->run();
					
					// executes after the command finishes
					if (!$process->isSuccessful()) {
						throw new \RuntimeException($process->getErrorOutput());
					}
					
					$processOutput = $process->getOutput();
					$mimeType = substr($processOutput, strrpos($processOutput, ":")+2);
				}
				
				$output->writeln ( "media id: " . $media->getId () . ". Mime-type is: " . $mimeType );
				if (! preg_match ( "/^video\/.*/", $mimeType ))
					continue;
				
				$count ++;
				if ($count % 20 == 0)
					$em->flush ();
				$type = Media::TYPE_VIDEO;
				$output->writeln ( "It is a wrongfully uploaded video" );
				$media->setType ( $type );
				$media->setIsReady ( Media::READY_NO );
				
				$dispatcher = $this->getContainer ()->get ( 'event_dispatcher' );
				$dispatcher->dispatch ( UploadEvent::EVENT_UPLOAD, new UploadEvent ( $media ) );
				$em->flush ();
				// if ($media->getThumbnailPath() == NULL)
				// {
				// $count ++;
				// try
				// {
				// $output->writeln('Generating thumbnail for media id:' . $media->getId());
				// $thumbnailTempFile = $transcoder->createThumbnail(
				// $media->getResource()
				// ->getAbsolutePath(), $media->getType());
				// $thumbnailFile = $media->getThumbnailRootDir() . "/" . $media->getResource()->getId() . ".png";
				// $fs->rename($thumbnailTempFile, $thumbnailFile, true);
				// $media->setThumbnailPath($media->getResource()
				// ->getId() . ".png");
				// }
				// catch (IOException $e)
				// {
				// $output->writeln("ERROR: " . $e->getTraceAsString());
				// }
				// catch (ExecutionFailureException $e)
				// {
				// $output->writeln("ERROR: " . $e->getTraceAsString());
				// }
				// if ($count % 20 == 0)
				// $em->flush();
				// }
			}
		}
	}
}
