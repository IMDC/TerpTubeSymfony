<?php
NAMESPACE IMDC\TERPTUBEBUNDLE\EVENTLISTENER;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use IMDC\TerpTubeBundle\Entity\MetaData;

use FFMpeg\FFMpeg;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Event\VideoReadyEvent;

class VideoReadyListener implements EventSubscriberInterface
{
	private $logger;
	private $doctrine;
	
	public function __construct($logger, $doctrine, $video_producer, $audio_producer)
	{
		$this->logger = $logger;
		$this->doctrine = $doctrine;
	}
	
	public static function getSubscribedEvents()
	{
		return array(
				VideoReadyEvent::EVENT_VIDEO_READY => 'onVideoReady',
		);
	}
	
	/**
	 * Trigerred when a video is converted and ready to use
	 * @param VideoReadyEvent $event
	 */
	public function onVideoReady(VideoReadyEvent $event)
	{
		//TODO if I need to do anything once the video is ready
	}
}
