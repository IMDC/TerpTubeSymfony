<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexConsumerOptions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MultiplexConsumerOptionsTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MultiplexConsumerOptionsTest extends WebTestCase
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $videoPath;

    /**
     * @var string
     */
    private $audioPath;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->rootDir = static::$kernel->getRootDir() . '/';
        $this->videoPath = $this->rootDir . '../../test_files/video.webm';
        $this->audioPath = $this->rootDir . '../../test_files/audio.wav';
    }

    public function testPack()
    {
        $opts = new MultiplexConsumerOptions();
        $opts->videoPath = $this->videoPath;
        $opts->audioPath = $this->audioPath;
        $opts->video = new File($this->videoPath);
        $opts->audio = new File($this->audioPath);
        $serialized = $opts->pack();

        $this->assertNull($opts->video);
        $this->assertNull($opts->audio);
        $this->assertNotNull($serialized);
    }

    public function testUnpack()
    {
        $opts = new MultiplexConsumerOptions();
        $opts->videoPath = $this->videoPath;
        $opts->audioPath = $this->audioPath;
        $serialized = $opts->pack();

        /** @var MultiplexConsumerOptions $cOpts */
        $cOpts = MultiplexConsumerOptions::unpack($serialized);

        $this->assertNotNull($cOpts);
        $this->assertEquals($this->videoPath, $cOpts->videoPath);
        $this->assertEquals($this->audioPath, $cOpts->audioPath);
        $this->assertNotNull($cOpts->video);
        $this->assertNotNull($cOpts->audio);
    }
}
