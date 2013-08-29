<?php
namespace IMDC\TerpTubeBundle\Consumer;
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

class UploadAudioConsumer extends ContainerAware implements ConsumerInterface
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
		$resourceFile = $media->getResource();
						
		$tempDir = $resourceFile->getUploadRootDir() . '/temp/' . $resourceFile->getId();
		$umask = umask();
		umask(0000);
		if (file_exists($tempDir))
			Utils::delTree($tempDir);
		mkdir($tempDir);
		umask($umask);
		$dir = getcwd();
		chdir($tempDir);
		$this->logger->info(getcwd());
		
		//Grab the width/height first to convert to the nearest standard resolution.
		
		//Convert to aac
		$outputFileAAC = $tempDir . '/' . $resourceFile->getId() . '.m4a';
		$this->logger->info("Transcoding " . $resourceFile->getAbsolutePath() ." to: " . $outputFileAAC);
		$aacFile = $this->transcoder->transcodeWithPreset($resourceFile->getAbsolutePath(), 'ffmpeg.aac_audio', $outputFileAAC);
		
		//Convert to webm ogg
		$outputFileWebm = $tempDir . '/' . $resourceFile->getId() . '.webm';
		$this->logger->info("Transcoding " . $resourceFile->getAbsolutePath() ." to: " . $outputFileWebm);
		$webmFile = $this->transcoder->transcodeWithPreset($resourceFile->getAbsolutePath(), 'ffmpeg.webm_audio', $outputFileWebm);
		
		//Create a thumbnail
		
		chdir($dir);
		if ($aacFile === null || $webmFile === null)
		{
			//The message is returned back to the queue
			return false;
		}
		
		$audioDuration = $this->ffprobe->streams($outputFileAAC)->audios()->first()->get('duration');
		$fileSize = filesize($outputFileAAC);
		
		$metaData->setDuration($audioDuration);
		$metaData->setSize($fileSize);
		
		unlink($resourceFile->getAbsolutePath());
		rename($aacFile, $resourceFile->getUploadRootDir() . '/' . $resourceFile->getId() . '.m4a');
		rename($webmFile, $resourceFile->getUploadRootDir() . '/' . $resourceFile->getId() . '.webm');
		Utils::delTree($tempDir);
		
		$resourceFile->setPath('m4a');
		$resourceFile->setWebmExtension('webm');
		$media->setIsReady(Media::READY_YES);
		
		$em->flush();
		
		$this->logger->info("Transcoding complete!");
		return true;
	}
}
