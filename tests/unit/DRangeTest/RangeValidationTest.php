<?php
namespace jrdev\DRangeTest;

use jrdev\DRange;

class RangeValidationTest extends \Codeception\Test\Unit
{
    /**
     * @var \jrdev\UnitTester
     */
    protected $tester;

    public function testAddInvalidRangeThrows()
    {
        $drange = new DRange(1, 5);
        $this->expectException(\UnexpectedValueException::class);
        $drange->add(10, 5);
    }

    public function testSubtractInvalidRangeThrows()
    {
        $drange = new DRange(1, 5);
        $this->expectException(\UnexpectedValueException::class);
        $drange->subtract(10, 5);
    }

    public function testSelfAddAndSubtract()
    {
        $drange = (new DRange(1, 5))->add(10, 15);
        $drange->add($drange);
        $this->assertEquals('[ 1-5, 10-15 ]', (string)$drange);
        $this->assertEquals(11, count($drange));

        $drange->subtract($drange);
        $this->assertEquals('[  ]', (string)$drange);
        $this->assertEquals(0, count($drange));
    }
}
