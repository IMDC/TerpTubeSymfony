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
    public function configure ()
    {
        $width = 1280;
        $height = 720;
        $this->setOptions(
                array(
                        '-vcodec' => 'libx264',
                        '-preset' => 'slow',
                        '-crf' => '22',
                        '-vb' => '1M',
                        
                        // '-vf' => 'scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih),
                        // pad=$width:$height:($width-iw*min($width/iw\,$height/ih))/2:($height-ih*min($width/iw\,$height/ih))/2',
                        '-vf' => "scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih), scale=trunc(in_w/2)*2:trunc(in_h/2)*2",
//                         '-vf' => "scale=-1:$height, scale=trunc(in_w/2)*2:trunc(in_h/2)*2",
                        '-strict' => 'experimental',
                        '-acodec' => 'aac',
                        '-ab' => '128k',
                        '-cutoff' => '15000',
                        '-bufsize' => '2000k',
                        '-threads' => '0',
                        '-r' => '25',
                        '-g' => '10'
                ));
    }

    protected function buildOutputDefinition ()
    {
        return new FileHandlerDefinition(
                array(
                        'requiredType' => 'file',
                        'requiredExtension' => 'mp4',
                        'inheritInputExtension' => false
                ));
    }
}
