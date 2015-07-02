<?php
namespace IMDC\TerpTubeBundle\Event;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\EventDispatcher\Event;

class VideoReadyEvent extends Event
{
	protected $media;
	const EVENT_VIDEO_READY = "imdc_terptube.event.videoReadyEvent";
	
	public function __construct(Media $media)
	{
		$this->media = $media;
	}
	
	public function getMedia()
	{
		return $this->media;
	}
}
