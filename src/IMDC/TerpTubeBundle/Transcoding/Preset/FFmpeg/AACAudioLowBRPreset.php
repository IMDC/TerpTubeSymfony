<?php

namespace IMDC\TerpTubeBundle\Transcoding\Preset\FFmpeg;
use AC\Transcoding\Preset\FFmpeg\BasePreset;

use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class AACAudioLowBRPreset extends BasePreset
{
	protected $key = "ffmpeg.aac_audio_low_audio_br";
	protected $name = "X264 Video Preset";
	protected $description = "A ffmpeg preset that takes an audio file and exports it to an m4a AAC encoded audio file";

	/**
	 * Specify the options for this specific preset
	 */
	public function configure()
	{
		$this
				->setOptions(
						array('-strict' => 'experimental', '-acodec' => 'aac', '-ab' => '32k', '-cutoff' => '15000',
								'-threads' => '0',));
	}

	protected function buildOutputDefinition()
	{
		return new FileHandlerDefinition(
				array('requiredType' => 'file', 'requiredExtension' => 'm4a', 'inheritInputExtension' => false,));
	}
}
