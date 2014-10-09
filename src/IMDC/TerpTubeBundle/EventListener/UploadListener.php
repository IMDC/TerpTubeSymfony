<?php
namespace IMDC\TerpTubeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use IMDC\TerpTubeBundle\Entity\MetaData;

use FFMpeg\FFMpeg;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Event\UploadEvent;

class UploadListener implements EventSubscriberInterface
{
	private $logger;
	private $doctrine;
	private $video_producer;
	private $audio_producer;
	
	public function __construct($logger, $doctrine, $video_producer, $audio_producer)
	{
		$this->logger = $logger;
		$this->doctrine = $doctrine;
		$this->video_producer = $video_producer;
		$this->audio_producer = $audio_producer;
	}
	
	public static function getSubscribedEvents()
	{
		return array(
				UploadEvent::EVENT_UPLOAD => 'onUpload',
		);
	}
	
	/**
	 * Trigerred when a file is uploaded
	 * @param UploadEvent $event
	 */
	public function onUpload(UploadEvent $event)
	{
		//TODO look into resizing images
		$media = $event->getMedia();
		$mediaType = $media->getType();
		//$ffmpeg = FFMpeg::create();

		$metaData = new MetaData();
		$fileSize = filesize($media->getResource()->getAbsolutePath());
		
		
		$metaData->setTimeUploaded(new \DateTime('now'));
		$metaData->setSize(-1);
		
		$em = $this->doctrine->getManager();
		//Transcode the different types and populate the metadata for the proper type
		if ($mediaType == Media::TYPE_AUDIO)
		{
			$em->persist($metaData);
			$media->setMetaData($metaData);
			$em->flush();
			
			$this->logger->info("Uploaded an audio media");
			$message = array('media_id'=> $media->getId());
			$this->audio_producer->publish(serialize($message));
			return;
			
		}
		else if ($mediaType == Media::TYPE_VIDEO)
		{
			$em->persist($metaData);
			$media->setMetaData($metaData);
			$em->flush();
			
			$this->logger->info("Uploaded a video media");
			//Send the Async Message
			$message = array('media_id'=> $media->getId());
			$this->video_producer->publish(serialize($message));
			return;
		}
		else if ($mediaType == Media::TYPE_IMAGE)
		{
			$this->logger->info("Uploaded an image media");
			
			$imageSize = getimagesize($media->getResource()->getAbsolutePath());
			$metaData->setWidth($imageSize[0]);
			$metaData->setHeight($imageSize[1]);
			$media->setIsReady(Media::READY_YES);
			$metaData->setSize($fileSize);
		}
		else
		{
			$this->logger->info("Uploaded something");
			$metaData->setSize($fileSize);
		}
		$em->persist($metaData);
		$media->setMetaData($metaData);
			
		$em->flush();
	}
}
