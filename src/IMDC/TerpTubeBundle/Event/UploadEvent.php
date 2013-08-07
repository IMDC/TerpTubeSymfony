<?php
namespace IMDC\TerpTubeBundle\Event;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\EventDispatcher\Event;
use Acme\StoreBundle\Order;

class UploadEvent extends Event
{
	protected $media;
	const EVENT_UPLOAD = "imdc_terptube.event.uploadEvent";
	
	public function __construct(Media $media)
	{
		$this->media = $media;
	}
	
	public function getMedia()
	{
		return $this->media;
	}
}
