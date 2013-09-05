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

class UploadVideoConsumer extends ContainerAware implements ConsumerInterface
{
	private $logger;
	private $doctrine;
 	private $ffprobe;
	private $transcoder;

	public function __construct($logger, $doctrine, $transcoder)
	{
		$this->logger = $logger;
		$this->doctrine = $doctrine;
		$this->transcoder = $transcoder;
 		$this->ffprobe = FFProbe::create();
	}

	public function execute(AMQPMessage $msg)
	{
		//Process video upload.
		//$msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
		$message = unserialize($msg->body);
		$mediaId = $message['media_id'];
		$em = $this->doctrine->getManager();
		/** @var $media IMDC\TerpTubeBundle\Entity\Media */
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
		$metaData = $media->getMetaData();
		$resource = $media->getResource();
		$resourceFile = new File($resource->getAbsolutePath());
						
		//Grab the width/height first to convert to the nearest standard resolution.
		
		$transcodingType = $media->getIsReady();
		if ($transcodingType == Media::READY_NO)
		{
			$this->logger->info("Transcoding " . $resourceFile->getRealPath());
			$mp4File = $this->transcoder->transcodeToX264($resourceFile, 'ffmpeg.x264_720p_video');
			$webmFile = $this->transcoder->transcodeToWebM($resourceFile, 'ffmpeg.webm_720p_video');
		}
		else if ($transcodingType == Media::READY_MPEG)
		{
			$this->logger->info("Transcoding " . $resourceFile->getRealPath());
			$webmFile = $this->transcoder->transcodeToWebM($resourceFile, 'ffmpeg.webm_720p_video');
			$mp4File = $resourceFile;
		}
		else if ($transcodingType == Media::READY_WEBM)
		{
			$this->logger->info("Transcoding " . $resourceFile->getRealPath());
			$mp4File = $this->transcoder->transcodeToX264($resourceFile, 'ffmpeg.x264_720p_video');
			$webmFile = $resourceFile;
		}
		//Create a thumbnail
		
		$videoWidth = $this->ffprobe->streams($mp4File->getRealPath())->videos()->first()->get('width');
		$videoHeight = $this->ffprobe->streams($mp4File->getRealPath())->videos()->first()->get('height');
		$videoDuration = $this->ffprobe->streams($mp4File->getRealPath())->videos()->first()->get('duration');
		$fileSize = filesize($mp4File->getRealPath());
		
		$metaData->setWidth($videoWidth);
		$metaData->setHeight($videoHeight);
		$metaData->setDuration($videoDuration);
		$metaData->setSize($fileSize);
		
		if ($resourceFile->getRealPath() != $mp4File->getRealPath() && $resourceFile->getRealPath() != $webmFile->getRealPath())
			unlink($resourceFile->getRealPath());
		rename($webmFile, $resource->getUploadRootDir() . '/' . $resource->getId() . '.webm');
		rename($mp4File, $resource->getUploadRootDir() . '/' . $resource->getId() . '.mp4');
		
		
		$resource->setPath('mp4');
		$resource->setWebmExtension('webm');
		$media->setIsReady(Media::READY_YES);
		
		$em->flush();
		
		$this->logger->info("Transcoding complete!");
		return true;
	}
}
