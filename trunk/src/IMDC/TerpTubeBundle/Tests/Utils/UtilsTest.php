<?php

namespace IMDC\TerpTubeBundle\Tests\Utils;

use IMDC\TerpTubeBundle\Utils\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testOrderMediaEmpty()
    {
        $mediaCollection = array();
        $displayOrder = unserialize('N;');

        $ordered = Utils::orderMedia($mediaCollection, $displayOrder);

        $this->assertEquals(array(), $ordered);
    }
}
