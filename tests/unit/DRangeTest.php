<?php
namespace jrdev;

use jrdev\DRange\SubRange;

class DRangeTest extends \Codeception\Test\Unit
{
    /**
     * @var \jrdev\UnitTester
     */
    protected $tester;

    public function testAddSingleNumbers()
    {
        $drange = new DRange(5);
        $this->assertEquals('[ 5 ]', $drange);

        $drange->add(6);
        $this->assertEquals('[ 5-6 ]', $drange);

        $drange->add(8);
        $this->assertEquals('[ 5-6, 8 ]', $drange);

        $drange->add(7);
        $this->assertEquals('[ 5-8 ]', $drange);

        $this->assertEquals(4, count($drange));
    }

    public function testAddRanges()
    {
        $drange = new DRange(1, 5);
        $this->assertEquals('[ 1-5 ]', $drange);

        $drange->add(6, 10);
        $this->assertEquals('[ 1-10 ]', $drange);

        $drange->add(15, 20);
        $this->assertEquals('[ 1-10, 15-20 ]', $drange);

        $drange->add(0, 14);
        $this->assertEquals('[ 0-20 ]', $drange);

        $this->assertEquals(21, count($drange));
    }

    public function testAddDRangeInstances()
    {
        $drange = new DRange(1, 5);
        $drange->add(15, 20);

        $drange2 = new DRange(6);
        $drange2->add(17, 30);

        $drange->add($drange2);
        $this->assertEquals('[ 1-6, 15-30 ]', $drange);

        $this->assertEquals(22, count($drange));
    }

    public function testAddSubRangeInstances()
    {
        $drange = new DRange(1, 5);
        $drange->add(3, 11);
        $drange->add(new SubRange(2, 20));
        $this->assertEquals('[ 1-20 ]', $drange);

        $this->assertEquals(20, count($drange));
    }

    public function testSubtractSingleNumbers()
    {
        $drange = new DRange(1, 10);
        $drange->subtract(5);
        $this->assertEquals('[ 1-4, 6-10 ]', $drange);

        $drange->subtract(7);
        $this->assertEquals('[ 1-4, 6, 8-10 ]', $drange);

        $drange->subtract(6);
        $this->assertEquals('[ 1-4, 8-10 ]', $drange);

        $this->assertEquals(7, count($drange));
    }

    public function testSubtractRanges()
    {
        $drange = new DRange(1, 100);
        $drange->subtract(5, 15);
        $this->assertEquals('[ 1-4, 16-100 ]', $drange);

        $drange->subtract(90, 200);
        $this->assertEquals('[ 1-4, 16-89 ]', $drange);

        $this->assertEquals(78, count($drange));
    }

    public function testSubtractDRangeInstances()
    {
        $drange = new DRange(0, 100);
        $drange2 = new DRange(6);

        $drange2->add(17, 30);
        $drange->subtract($drange2);
        $this->assertEquals('[ 0-5, 7-16, 31-100 ]', $drange);

        $this->assertEquals(86, count($drange));
    }

    public function testSubtractSubRangeInstances()
    {
        $drange = new DRange(15, 22);
        $drange2 = new DRange\SubRange(6, 17);

        $drange->subtract($drange2);
        $this->assertEquals('[ 18-22 ]', $drange);

        $this->assertEquals(5, count($drange));
    }

    public function testIndex()
    {
        $drange = new DRange(0, 9);
        $drange->add(20, 29);
        $drange->add(40, 49);

        $this->assertEquals(5, $drange->index(5));
        $this->assertEquals(25, $drange->index(15));
        $this->assertEquals(45, $drange->index(25));
        $this->assertNull($drange->index(55));
        $this->assertEquals(30, count($drange));
    }

    public function testClone()
    {
        $drange = new DRange(0, 9);
        $drange2 = clone($drange);

        $drange2->subtract(5);
        $this->assertEquals('[ 0-9 ]', $drange);
        $this->assertEquals('[ 0-4, 6-9 ]', $drange2);
    }

    public function testIntersectSingleNumbers()
    {
        $drange1 = new DRange(1, 5);
        $drange2 = new DRange(3, 7);

        $result = $drange1->intersect($drange2);
        $this->assertEquals('[ 3-5 ]', $result);
        $this->assertEquals(3, count($result));
    }

    public function testIntersectRanges()
    {
        $drange1 = new DRange(1, 5);
        $drange1->add(10, 15);

        $drange2 = new DRange(3, 12);

        $result = $drange1->intersect($drange2);
        $this->assertEquals('[ 3-5, 10-12 ]', $result);
        $this->assertEquals(6, count($result));
    }

    public function testIntersectDisjoint()
    {
        $drange1 = new DRange(1, 5);
        $drange2 = new DRange(10, 15);

        $result = $drange1->intersect($drange2);
        $this->assertEquals('[  ]', strval($result));
        $this->assertEquals(0, count($result));
    }

    public function testIntersectWithSubRange()
    {
        $drange = new DRange(1, 10);
        $drange->add(20, 30);

        $result = $drange->intersect(new SubRange(5, 25));
        $this->assertEquals('[ 5-10, 20-25 ]', $result);
        $this->assertEquals(12, count($result));
    }

    public function testIntersectWithSingleNumber()
    {
        $drange = new DRange(1, 5);
        $drange->add(10, 15);

        $result = $drange->intersect(3, 12);
        $this->assertEquals('[ 3-5, 10-12 ]', $result);
        $this->assertEquals(6, count($result));

        $result2 = $drange->intersect(4);
        $this->assertEquals('[ 4 ]', $result2);
        $this->assertEquals(1, count($result2));
    }
}
