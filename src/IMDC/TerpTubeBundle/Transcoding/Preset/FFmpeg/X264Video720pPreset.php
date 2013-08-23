<?php

namespace IMDC\TerpTubeBundle\Transcoding\Preset\FFmpeg;

use AC\Transcoding\Preset\FFmpeg\BasePreset;

use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class X264Video720pPreset extends BasePreset
{
    protected $key = "ffmpeg.x264_720p_video";
    protected $name = "X264 Video Preset";
    protected $description = "A ffmpeg preset that takes a video and exports it to a 720p x264 encoded video file";

    /**
     * Specify the options for this specific preset
     */
    public function configure()
    {
        $this->setOptions(array(
            '-vcodec' => 'libx264',
            '-preset' => 'slow',
            '-crf' => '22',
            '-b:v' => '1000k',
        	'-vf' => 'scale=trunc(oh*a*2)/2:720',
        	'-maxrate' => '1000k',
        	'-acodec' =>'libfaac',
        	'-b:a' =>'128k',
        	'-bufsize' =>'2000k',
        	'-threads' => '0',
        	'-r' => '30',
        	'-g' => '10',
        ));
    }

    protected function buildOutputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'requiredExtension' => 'mp4',
            'inheritInputExtension' => false,
        ));
    }
}
