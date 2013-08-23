<?php

namespace IMDC\TerpTubeBundle\Transcoding\Preset\FFmpeg;

use AC\Transcoding\Preset\FFmpeg\BasePreset;

use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class WebMVideo720pPreset extends BasePreset
{
    protected $key = "ffmpeg.webm_720p_video";
    protected $name = "Web M Video Preset";
    protected $description = "A ffmpeg preset that takes a video and exports it to a 720p webm encoded video file";

    /**
     * Specify the options for this specific preset
     */
    public function configure()
    {
        $this->setOptions(array(
            '-vcodec' => 'libvpx',
            '-preset' => 'slow',
            '-crf' => '10',
            '-b:v' => '1000k',
        	'-vf' => 'scale=trunc(oh*a*2)/2:720',
        	'-maxrate' => '1000k',
        	'-acodec' =>'libvorbis',
        	'-b:a' =>'128k',
        	'-bufsize' =>'2000k',
        	'-threads' => '7',
        	'-r' => '30',
        	'-g' => '10',
        ));
    }

    protected function buildOutputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'requiredExtension' => 'webm',
            'inheritInputExtension' => false,
        ));
    }
}
