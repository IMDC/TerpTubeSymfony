<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Symfony\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Utils\Utils;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\FfmpegFormats\H264;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Coordinate\Dimension;
use Symfony\Component\DependencyInjection\ContainerAware;
use FFMpeg\FFMpeg;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Filesystem;

class UploadVideoConsumer extends ContainerAware implements ConsumerInterface {
	private $logger;
	private $doctrine;
	private $ffprobe;
	private $transcoder;
	public function __construct($logger, $doctrine, $transcoder) {
		$this->logger = $logger;
		$this->doctrine = $doctrine;
		$this->transcoder = $transcoder;
		$this->ffprobe = FFProbe::create ();
	}
	
	public function checkForPendingOperations($mediaId)
	{
		$em = $this->doctrine->getManager ();
		$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
		if ($media->getPendingOperations()!=null && count($media->getPendingOperations()) > 0 )
			return true;
		else 
			return false;
	}
	
	public function executePendingOperations($mediaId)
	{
		$em = $this->doctrine->getManager ();
		$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
		$pendingOperations = $media->getPendingOperations();
		foreach ($pendingOperations as $pendingOperation)
		{
			$operation = explode(",",$pendingOperation);
			$operationType = $operation[0];
			if ($operationType == "trim")
			{
				$operationMediaType = $operation[1];
				$resource = $media->getResource ();
				if ($operationMediaType == "mp4")
				{
					$inputFile = $resource->getAbsolutePath();
				}
				else if ($operationMediaType == "webm")
				{
					$inputFile = $resource->getAbsolutePathWebm();
				}
				$startTime = $operation[2];
				$endTime = $operation[3];
				$this->transcoder->trimVideo ( $inputFile, $startTime, $endTime );
				$this->logger->info ( "Transcoding operation ".$pendingOperation." completed!" );
			}
			else 
			{
				$this->logger->error ( "Unknown operation ".$pendingOperation."!" );
			}
			
		}
		//FIXME may have a race condition if pending operations are updated elsewhere
		$pendingOperations = array();
		$media->setPendingOperations($pendingOperations);
		$em->flush();
	}
	
	public function execute(AMQPMessage $msg) {
		// Process video upload.
		// $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
		try {
			$message = unserialize ( $msg->body );
			$mediaId = $message ['media_id'];
			$em = $this->doctrine->getManager ();
			/**
			 *
			 * @var $media IMDC\TerpTubeBundle\Entity\Media
			 */
			$media = $em->getRepository ( 'IMDCTerpTubeBundle:Media' )->find ( $mediaId );
			$metaData = $media->getMetaData ();
			$resource = $media->getResource ();
			$resourceFile = new File ( $resource->getAbsolutePath () );
			
			// Grab the width/height first to convert to the nearest standard resolution.
			
			$transcodingType = $media->getIsReady ();
			if ($transcodingType == Media::READY_NO) {
				$this->logger->info ( "Transcoding " . $resourceFile->getRealPath () );
				$mp4File = $this->transcoder->transcodeToX264 ( $resourceFile, 'ffmpeg.x264_720p_video' );
				$webmFile = $this->transcoder->transcodeToWebM ( $resourceFile, 'ffmpeg.webm_720p_video' );
			} else if ($transcodingType == Media::READY_MPEG) {
				$this->logger->info ( "Transcoding " . $resourceFile->getRealPath () );
				$webmFile = $this->transcoder->transcodeToWebM ( $resourceFile, 'ffmpeg.webm_720p_video' );
				$mp4File = $resourceFile;
			} else if ($transcodingType == Media::READY_WEBM) {
				$this->logger->info ( "Transcoding " . $resourceFile->getRealPath () );
				$mp4File = $this->transcoder->transcodeToX264 ( $resourceFile, 'ffmpeg.x264_720p_video' );
				$webmFile = $resourceFile;
			}
			else 
			{
				//Already Transcoded should not be here
				$this->logger->error ( "Should not be in this place of transcoding when everything is already completed!" );
			}
			// Create a thumbnail
			
			$videoWidth = $this->ffprobe->streams ( $mp4File->getRealPath () )->videos ()->first ()->get ( 'width' );
			$videoHeight = $this->ffprobe->streams ( $mp4File->getRealPath () )->videos ()->first ()->get ( 'height' );
			$videoDuration = $this->ffprobe->streams ( $mp4File->getRealPath () )->videos ()->first ()->get ( 'duration' );
			
			$mp4DestinationFile = $resource->getUploadRootDir () . '/' . $resource->getId () . '.mp4';
			
			// Why is this check here
			if (file_exists ( $mp4DestinationFile )) {
				$destinationVideoDuration = $this->ffprobe->streams ( $mp4DestinationFile )->videos ()->first ()->get ( 'duration' );
				if ($videoDuration > $destinationVideoDuration) {
					$mp4File = new File ( $mp4DestinationFile );
					$videoDuration = $destinationVideoDuration;
				}
			}
			$fileSize = filesize ( $mp4File->getRealPath () );
			$metaData->setWidth ( $videoWidth );
			$metaData->setHeight ( $videoHeight );
			$metaData->setDuration ( $videoDuration );
			$metaData->setSize ( $fileSize );
			
			// Correct the permissions to 664
			$old = umask ( 0 );
			chmod ( $mp4File->getRealPath (), 0664 );
			chmod ( $webmFile->getRealPath (), 0664 );
			umask ( $old );
			
			if ($resourceFile->getRealPath () != $mp4File->getRealPath () && $resourceFile->getRealPath () != $webmFile->getRealPath ())
				unlink ( $resourceFile->getRealPath () );
			$fs = new Filesystem ();
			if ($resourceFile->getRealPath () != $webmFile->getRealPath ())
				$fs->rename ( $webmFile, $resource->getUploadRootDir () . '/' . $resource->getId () . '.webm' );
			if ($resourceFile->getRealPath () != $mp4File->getRealPath ())
				$fs->rename ( $mp4File, $resource->getUploadRootDir () . '/' . $resource->getId () . '.mp4' );
			
			$resource->setPath ( 'mp4' );
			$resource->setWebmExtension ( 'webm' );
			$media->setIsReady ( Media::READY_YES );
			
			$em->flush ();
			
			if ($this->checkForPendingOperations($mediaId))
			{
				$this->executePendingOperations($mediaId);
			}
			
			$this->logger->info ( "Transcoding complete!" );
		} catch ( Exception $e ) {
			$this->logger->error ( $e->getTraceAsString () );
			return false;
		}
		return true;
	}
}
