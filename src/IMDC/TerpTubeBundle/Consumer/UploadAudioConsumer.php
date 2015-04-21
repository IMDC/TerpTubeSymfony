<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Symfony\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Utils\Utils;
use IMDC\TerpTubeBundle\Entity\Media;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Coordinate\Dimension;
use Symfony\Component\DependencyInjection\ContainerAware;
use FFMpeg\FFMpeg;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Filesystem;

class UploadAudioConsumer extends ContainerAware implements ConsumerInterface
{
	private $logger;
	private $doctrine;
	private $ffprobe;
	private $transcoder;
	
	const MIN_AUDIO_BR = 100000;
	
	public function __construct($logger, $doctrine, $transcoder)
	{
		$this->logger = $logger;
		$this->doctrine = $doctrine;
		$this->transcoder = $transcoder;
		$this->ffprobe = FFProbe::create();
	}
	public function checkForPendingOperations($mediaId)
	{
		$em = $this->doctrine->getManager();
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
		if ($media->getPendingOperations() != null && count($media->getPendingOperations()) > 0)
			return true;
		else
			return false;
	}
	public function executePendingOperations($mediaId)
	{
		$em = $this->doctrine->getManager();
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
		$pendingOperations = $media->getPendingOperations();
		foreach ( $pendingOperations as $pendingOperation )
		{
			$operation = explode(",", $pendingOperation);
			$operationType = $operation [0];
			if ($operationType == "trim")
			{
				$operationMediaType = $operation [1];
				$resource = $media->getResource();
				if ($operationMediaType == "mp4")
				{
					$inputFile = $resource->getAbsolutePath();
				}
				else if ($operationMediaType == "webm")
				{
					//$inputFile = $resource->getAbsolutePathWebm();
					$inputFile = $resource->getAbsolutePath();
				}
				$startTime = $operation [2];
				$endTime = $operation [3];
				$this->transcoder->trimVideo($inputFile, $startTime, $endTime);
				$this->logger->info("Transcoding operation " . $pendingOperation . " completed!");
			}
			else
			{
				$this->logger->error("Unknown operation " . $pendingOperation . "!");
			}
		}
		// FIXME may have a race condition if pending operations are updated elsewhere
		$pendingOperations = array ();
		$media->setPendingOperations($pendingOperations);
		$em->flush();
	}
	public function execute(AMQPMessage $msg)
	{
		// Process video upload.
		// $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
		try
		{
			$message = unserialize($msg->body);
			$mediaId = $message ['media_id'];
			$em = $this->doctrine->getManager();
			/**
			 *
			 * @var $media IMDC\TerpTubeBundle\Entity\Media
			 */
			$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
			if (empty($media))
			{
				// Can happen if media is deleted before transcoding can be executed
				$this->logger->info("Media with ID=$mediaId does not exist and cannot be transcoded!");
				return true;
			}
			$metaData = $media->getMetaData();
			$resource = $media->getResource();
			$resourceFile = new File($resource->getAbsolutePath());
			
			
			try
			{
			    $isVideo = $this->transcoder->checkAudioFile ( $resourceFile );
			    if (! $isVideo)
			    {
			        // not a video so don't hold up the queue
			        $this->logger->error ( "Error with the audio or not a valid audio file: $resourceFile!" );
			        return true;
			    }
			}
			catch ( \Exception $e )
			{
			    // not a video so don't hold up the queue
			    $this->logger->error ( "Error with the audio or not a valid audio file: $resourceFile!" );
			    return true;
			}
			
			$transcodingType = $media->getIsReady();
			
			if ($transcodingType == Media::READY_NO)
			{
				$this->logger->info("Transcoding " . $resourceFile->getRealPath());
				$mp4File = $this->transcoder->transcodeAudioToX264($resourceFile, 'ffmpeg.aac_audio');
				$webmFile = $this->transcoder->transcodeAudioToWebM($resourceFile, 'ffmpeg.webm_audio');
			}
			else if ($transcodingType == Media::READY_MPEG)
			{
				$this->logger->info("Transcoding " . $resourceFile->getRealPath());
				$webmFile = $this->transcoder->transcodeAudioToWebM($resourceFile, 'ffmpeg.webm_audio');
				$mp4File = $resourceFile;
			}
			else if ($transcodingType == Media::READY_WEBM)
			{
				$this->logger->info("Transcoding " . $resourceFile->getRealPath());
				$mp4File = $this->transcoder->transcodeAudioToX264($resourceFile, 'ffmpeg.aac_audio');
				$webmFile = $resourceFile;
			}
			else
			{
				// Already Transcoded should not be here
				$this->logger->error("Should not be in this place of transcoding when everything is already completed!");
				$webmFile = $resourceFile;
				$mp4File = $resourceFile;
			}
			
			if ($this->ffprobe->format($webmFile->getRealPath())->has('duration'))
				$videoDuration = $this->ffprobe->format($webmFile->getRealPath())->get('duration');
			else 
				$videoDuration = 0;
			$mp4DestinationFile = $resource->getUploadRootDir() . '/' . $resource->getId() . '.m4a';
			
			// Why is this check here
			if (file_exists($mp4DestinationFile))
			{
				if ($this->ffprobe->format($mp4DestinationFile)->has('duration'))
					$destinationVideoDuration = $this->ffprobe->format($mp4DestinationFile)->get('duration');
				else 
					$destinationVideoDuration = 0;
				if ($videoDuration > $destinationVideoDuration)
				{
					$mp4File = new File($mp4DestinationFile);
					$videoDuration = $destinationVideoDuration;
				}
			}
			$fileSize = filesize($webmFile->getRealPath());
			$metaData->setDuration($videoDuration);
			$metaData->setSize($fileSize);
			
			// Correct the permissions to 664
			$old = umask(0);
			chmod($mp4File->getRealPath(), 0664);
			chmod($webmFile->getRealPath(), 0664);
			umask($old);
			
			if ($resourceFile->getRealPath() != $mp4File->getRealPath() && $resourceFile->getRealPath() != $webmFile->getRealPath())
				unlink($resourceFile->getRealPath());
			$fs = new Filesystem();
			if ($resourceFile->getRealPath() != $webmFile->getRealPath())
				$fs->rename($webmFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
			if ($resourceFile->getRealPath() != $mp4File->getRealPath())
				$fs->rename($mp4File, $resource->getUploadRootDir() . '/' . $resource->getId() . '.m4a');
			
			$resource->setPath('m4a');
			//$resource->setWebmExtension('webm');
			$media->setIsReady(Media::READY_YES);
			
			$em->flush();
			
			if ($this->checkForPendingOperations($mediaId))
			{
				$this->executePendingOperations($mediaId);
			}
			
			$this->logger->info("Transcoding complete!");
		}
		catch ( Exception $e )
		{
			$this->logger->error($e->getTraceAsString());
			return false;
		}
		return true;
	}
}
