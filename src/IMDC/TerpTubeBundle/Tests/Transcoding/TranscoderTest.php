<?php

namespace IMDC\TerpTubeBundle\Tests\Transcoding;

use FFMpeg\FFProbe\DataMapping\StreamCollection;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TranscoderTest
 * @package IMDC\TerpTubeBundle\Tests\Transcoding
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class TranscoderTest extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testInstantiate()
    {
        $trancoder = $this->getTranscoder();

        $this->assertNotNull($trancoder);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Transcoding\Transcoder', $trancoder);
    }

    /**
     * @returns File
     */
    public function testMergeAudioVideo()
    {
        $rootDir = static::$kernel->getRootDir() . '/';
        copy($rootDir . '../../test_files/video.webm', '/tmp/video.webm');
        copy($rootDir . '../../test_files/audio.wav', '/tmp/audio.wav');

        $video = new File('/tmp/video.webm');
        $audio = new File('/tmp/audio.wav');
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

        return $merged;
    }

    /**
     * @depends testMergeAudioVideo
     * @param $file
     */
    public function testTrimVideo($file)
    {
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
        //return static::$kernel->getContainer()->get('imdc_terptube.transcoder');

        $container = static::$kernel->getContainer();

        $logger = $container->get('logger');
        $transcoder = $container->get('transcoder');
        $config = $container->getParameter('imdc_ffmpeg.config');

        return new Transcoder($logger, $transcoder, $config);
    }
}
