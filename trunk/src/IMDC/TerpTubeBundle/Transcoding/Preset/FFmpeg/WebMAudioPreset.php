<?php

namespace IMDC\TerpTubeBundle\Transcoding\Preset\FFmpeg;
use AC\Transcoding\Preset\FFmpeg\BasePreset;

use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class WebMAudioPreset extends BasePreset
{
	protected $key = "ffmpeg.webm_audio";
	protected $name = "Web M Audio Preset";
	protected $description = "A ffmpeg preset that takes an audio file and exports it to a webm ogg encoded audio file";

	/**
	 * Specify the options for this specific preset
	 */
	public function configure()
	{
		$this
				->setOptions(
						array(	'-acodec' => 'libvorbis', '-ab' => '128k',
								'-threads' => '7'));
	}

	protected function buildOutputDefinition()
	{
		return new FileHandlerDefinition(
				array('requiredType' => 'file', 'requiredExtension' => 'webm', 'inheritInputExtension' => false,));
	}
}
