<?php
namespace IMDC\TerpTubeBundle\Transcoding\Preset\FFmpeg;
use AC\Transcoding\Preset\FFmpeg\BasePreset;
use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class WebMVideoVGAPreset extends BasePreset
{

    protected $key = "ffmpeg.webm_vga_video";

    protected $name = "Web M Video Preset";

    protected $description = "A ffmpeg preset that takes a video and exports it to a vga 854x480 webm encoded video file";

    /**
     * Specify the options for this specific preset
     */
    public function configure ()
    {
        $width = 854;
        $height = 480;
        $this->setOptions(
                array(
                        '-vcodec' => 'libvpx',
                        '-preset' => 'slow',
                        '-crf' => '10',
                        '-vb' => '500k',
                        
                        // '-vf' => 'scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih),
                        // pad=$width:$height:($width-iw*min($width/iw\,$height/ih))/2:($height-ih*min($width/iw\,$height/ih))/2',
                        // '-vf' => "scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih)",
                        '-vf' => "scale=-1:$height",
                        '-maxrate' => '500k',
                        '-acodec' => 'libvorbis',
                        '-ab' => '128k',
                        '-bufsize' => '1000k',
                        '-threads' => '7',
                        '-r' => '25',
                        '-g' => '10'
                ));
    }

    protected function buildOutputDefinition ()
    {
        return new FileHandlerDefinition(
                array(
                        'requiredType' => 'file',
                        'requiredExtension' => 'webm',
                        'inheritInputExtension' => false
                ));
    }
}
