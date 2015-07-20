<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use IMDC\TerpTubeBundle\Consumer\Options\StatusConsumerOptions;

/**
 * Class StatusConsumerOptionsTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class StatusConsumerOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StatusConsumerOptions
     */
    private $opts;

    public function setUp()
    {
        $this->opts = new StatusConsumerOptions();
        $this->opts->status = 'done';
        $this->opts->who = get_class($this);
        $this->opts->what = 'Test';
        $this->opts->identifier = null; // nulls, when unpacked, should be empty strings
    }

    public function testPack()
    {
        $json = $this->opts->pack();
        $this->assertNotNull($json);
    }

    public function testUnpack()
    {
        $json = $this->opts->pack();
        $opts = StatusConsumerOptions::unpack($json);

        $this->assertNotNull($opts);
        $this->assertEquals($this->opts->status, $opts->status);
        $this->assertEquals($this->opts->who, $opts->who);
        $this->assertEquals($this->opts->what, $opts->what);
        $this->assertEquals($this->opts->identifier, '');
    }
}
