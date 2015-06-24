<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexConsumerOptions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MultiplexConsumerOptionsTest extends WebTestCase
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $video;

    /**
     * @var string
     */
    private $audio;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->rootDir = static::$kernel->getRootDir() . '/';
        $this->video = $this->rootDir . '../../test_files/video.webm';
        $this->audio = $this->rootDir . '../../test_files/audio.wav';
    }

    public function testPack()
    {
        $opts = new MultiplexConsumerOptions();
        $opts->videoPath = $this->video;
        $opts->audioPath = $this->audio;
        $opts->video = new File($this->video);
        $opts->audio = new File($this->audio);
        $serialized = $opts->pack();

        $this->assertNull($opts->video);
        $this->assertNull($opts->audio);
        $this->assertNotNull($serialized);
    }

    public function testUnpack()
    {
        $opts = new MultiplexConsumerOptions();
        $opts->videoPath = $this->video;
        $opts->audioPath = $this->audio;
        $serialized = $opts->pack();

        /** @var MultiplexConsumerOptions $cOpts */
        $cOpts = MultiplexConsumerOptions::unpack($serialized);

        $this->assertNotNull($cOpts);
        $this->assertEquals($this->video, $cOpts->videoPath);
        $this->assertEquals($this->audio, $cOpts->audioPath);
        $this->assertNotNull($cOpts->video);
        $this->assertNotNull($cOpts->audio);
    }
}
