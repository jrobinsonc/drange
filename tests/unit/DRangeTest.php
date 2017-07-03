<?php
namespace jrdev;

use jrdev\DRange\SubRange;

class DRangeTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \jrdev\UnitTester
     */
    protected $tester;

    public function testAddSets()
    {
        $this->specify('should allow adding numbers', function () {
            $drange = new DRange(5);
            $this->assertEquals('[ 5 ]', $drange);

            $drange->add(6);
            $this->assertEquals('[ 5-6 ]', $drange);

            $drange->add(8);
            $this->assertEquals('[ 5-6, 8 ]', $drange);

            $drange->add(7);
            $this->assertEquals('[ 5-8 ]', $drange);

            $this->assertEquals(4, count($drange));
        });

        $this->specify('should allow adding ranges of numbers', function () {
            $drange = new DRange(1, 5);
            $this->assertEquals('[ 1-5 ]', $drange);

            $drange->add(6, 10);
            $this->assertEquals('[ 1-10 ]', $drange);

            $drange->add(15, 20);
            $this->assertEquals('[ 1-10, 15-20 ]', $drange);

            $drange->add(0, 14);
            $this->assertEquals('[ 0-20 ]', $drange);

            $this->assertEquals(21, count($drange));
        });

        $this->specify('should allow adding instances of DRange', function () {
            $drange = new DRange(1, 5);
            $drange->add(15, 20);

            $drange2 = new DRange(6);
            $drange2->add(17, 30);

            $drange->add($drange2);
            $this->assertEquals('[ 1-6, 15-30 ]', $drange);

            $this->assertEquals(22, count($drange));
        });

        $this->specify('should allow adding instances of DRange\SubRange', function () {
            $drange = new DRange(1, 5);
            $drange->add(3, 11);
            $drange->add(new SubRange(2, 20));
            $this->assertEquals('[ 1-20 ]', $drange);

            $this->assertEquals(20, count($drange));
        });
    }

    public function testSubtractSets()
    {
        $this->specify('should allow subtracting numbers', function () {
            $drange = new DRange(1, 10);
            $drange->subtract(5);
            $this->assertEquals('[ 1-4, 6-10 ]', $drange);

            $drange->subtract(7);
            $this->assertEquals('[ 1-4, 6, 8-10 ]', $drange);

            $drange->subtract(6);
            $this->assertEquals('[ 1-4, 8-10 ]', $drange);

            $this->assertEquals(7, count($drange));
        });

        $this->specify('should allow subtracting ranges of numbers', function () {
            $drange = new DRange(1, 100);
            $drange->subtract(5, 15);
            $this->assertEquals('[ 1-4, 16-100 ]', $drange);

            $drange->subtract(90, 200);
            $this->assertEquals('[ 1-4, 16-89 ]', $drange);

            $this->assertEquals(78, count($drange));
        });

        $this->specify('should allow subtracting another DRange', function () {
            $drange = new DRange(0, 100);
            $drange2 = new DRange(6);

            $drange2->add(17, 30);
            $drange->subtract($drange2);
            $this->assertEquals('[ 0-5, 7-16, 31-100 ]', $drange);

            $this->assertEquals(86, count($drange));
        });

        $this->specify('should allow subtracting instances of DRange\SubRange', function () {
            $drange = new DRange(15, 22);
            $drange2 = new DRange\SubRange(6, 17);

            $drange->subtract($drange2);
            $this->assertEquals('[ 18-22 ]', $drange);

            $this->assertEquals(5, count($drange));
        });
    }

    public function testIndexSets()
    {
        $this->specify('should appropriately retrieve numbers in range by index', function () {
            $drange = new DRange(0, 9);
            $drange->add(20, 29);
            $drange->add(40, 49);

            $this->assertEquals(5, $drange->index(5));
            $this->assertEquals(25, $drange->index(15));
            $this->assertEquals(45, $drange->index(25));
            $this->assertNull($drange->index(55));
            $this->assertEquals(30, count($drange));
        });
    }

    public function testCloneSets()
    {
        $this->specify('should be able to clone a DRange that doesn\'t affect the original', function () {
            $drange = new DRange(0, 9);
            $drange2 = clone($drange);

            $drange2->subtract(5);
            $this->assertEquals('[ 0-9 ]', $drange);
            $this->assertEquals('[ 0-4, 6-9 ]', $drange2);
        });
    }
}
