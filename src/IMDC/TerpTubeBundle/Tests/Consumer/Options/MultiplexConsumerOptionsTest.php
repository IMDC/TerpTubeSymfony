<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File;
use IMDC\TerpTubeBundle\Consumer\Options\MultiplexConsumerOptions;
use IMDC\TerpTubeBundle\Tests\BaseWebTestCase;

/**
 * Class MultiplexConsumerOptionsTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MultiplexConsumerOptionsTest extends BaseWebTestCase
{
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
        $filesPath = $this->getContainer()->getParameter('imdc_terptube.tests.files_path');
        $this->videoPath = $filesPath . '/video.webm';
        $this->audioPath = $filesPath . '/audio.wav';
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
