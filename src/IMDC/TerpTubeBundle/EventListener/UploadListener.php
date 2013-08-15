<?php
NAMESPACE IMDC\TERPTUBEBUNDLE\EVENTLISTENER;

use IMDC\TerpTubeBundle\Entity\MetaData;

use FFMpeg\FFMpeg;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Event\UploadEvent;

class UploadListener
{
	private $logger;
	private $doctrine;
	
	public function __construct($logger, $doctrine)
	{
		$this->logger = $logger;
		$this->doctrine = $doctrine;
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
		
		$metaData->setSize($fileSize);
		$metaData->setTimeUploaded(new \DateTime('now'));
		//Transcode the different types and populate the metadata for the proper type
		if ($mediaType == Media::TYPE_AUDIO)
		{
			$this->logger->info("Uploaded an audio media");
		}
		else if ($mediaType == Media::TYPE_VIDEO)
		{
			$this->logger->info("Uploaded a video media");
		}
		else if ($mediaType == Media::TYPE_IMAGE)
		{
			$this->logger->info("Uploaded an image media");
			
			$imageSize = getimagesize($media->getResource()->getAbsolutePath());
			$metaData->setWidth($imageSize[0]);
			$metaData->setHeight($imageSize[1]);
			$media->setIsReady(Media::READY_YES);
		}
		else
		{
			$this->logger->info("Uploaded something");
		}
		
		$em = $this->doctrine->getManager();
		$em->persist($metaData);
		$media->setMetaData($metaData);
			
		$em->flush();
	}
}
