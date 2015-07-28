<?php

namespace IMDC\TerpTubeBundle\Tests\Transcoding;

use FFMpeg\FFProbe\DataMapping\StreamCollection;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TranscoderTest
 * @package IMDC\TerpTubeBundle\Tests\Transcoding
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TranscoderTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->reloadDatabase(array(
            'IMDC\TerpTubeBundle\DataFixtures\ORM\LoadTestMedia'
        ));
    }

    public function testInstantiate()
    {
        $trancoder = $this->getTranscoder();

        $this->assertNotNull($trancoder);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Transcoding\Transcoder', $trancoder);
    }

    public function testMergeAudioVideo()
    {
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $videoFilename = 'video.webm';
        $audioFilename = 'audio.wav';
        $tempVideo = tempnam('/tmp', 'hello_') . $videoFilename;
        $tempAudio = tempnam('/tmp', 'hello_') . $audioFilename;
        copy($filesPath . '/' . $videoFilename, $tempVideo);
        copy($filesPath . '/' . $audioFilename, $tempAudio);
        $video = new File($tempVideo);
        $audio = new File($tempAudio);

        $transcoder = $this->getTranscoder();
        $merged = $transcoder->mergeAudioVideo($audio, $video);
        $this->assertNotNull($merged);

        /* @var $streams StreamCollection */
        $streams = $transcoder->getFFprobe()->streams($merged->getPathname());
        $it = $streams->getIterator();
        $stream0 = $it->current();
        $it->next();
        $stream1 = $it->current();

        $this->assertCount(2, $streams, 'there should be 2 streams');
        $this->assertCount(1, $streams->videos(), 'there should be 1 video stream');
        $this->assertCount(1, $streams->audios(), 'there should be 1 audio stream');
        $this->assertTrue($stream0->isAudio(), 'the audio stream should be at index 0');
        $this->assertTrue($stream1->isVideo(), 'the video stream should be at index 1');
    }

    public function testTrimVideo()
    {
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $filename = 'video_audio.webm';
        $temp = tempnam('/tmp', 'hello_') . $filename;
        copy($filesPath . '/' . $filename, $temp);
        $file = new File($temp);

        $startTime = 0.4;
        $endTime = 2.2;
        $expectedLength = $endTime - $startTime;
        $transcoder = $this->getTranscoder();

        $success = $transcoder->trimVideo($file, $startTime, $endTime);
        $this->assertTrue($success, 'trimming should not have failed');

        /* @var $streams StreamCollection */
        $streams = $transcoder->getFFprobe()->streams($file->getPathname());
        $format = $transcoder->getFFprobe()->format($file->getPathname());
        $lengthDiff = abs($expectedLength - floatval($format->get('duration')));

        $this->assertCount(2, $streams, 'there should be 2 streams');
        $this->assertLessThanOrEqual(0.1, $lengthDiff, 'trim should be within tolerance of 0.1');
    }

    /**
     * @return Transcoder
     */
    private function getTranscoder()
    {
        $logger = $this->getContainer()->get('logger');
        $transcoder = $this->getContainer()->get('transcoder');
        $config = $this->getContainer()->getParameter('imdc_ffmpeg.config');

        return new Transcoder($logger, $transcoder, $config);
    }
}
