<?php
namespace jrdev\DRangeTest;

use \jrdev\DRange\SubRange;

class SubRangeTest extends \Codeception\Test\Unit
{
    /**
     * @var \jrdev\UnitTester
     */
    protected $tester;

    public function testInstanciate()
    {
        $subRange = new SubRange(5, 7);
        $this->assertEquals('5-7', $subRange);
    }

    public function testInstanciateThrowsOnInvalidRange()
    {
        $this->expectException(\UnexpectedValueException::class);
        new SubRange(7, 5);
    }

    public function testClone()
    {
        $subRange = new SubRange(5, 7);
        $subRange2 = clone($subRange);

        $subRange = $subRange->add(new SubRange(7, 9));
        $subRange2 = $subRange2->add(new SubRange(1, 10));

        $this->assertEquals('5-9', $subRange);
        $this->assertEquals('1-10', $subRange2);
    }

    public function testToStringSingleNumber()
    {
        $subRange = new SubRange(5, 5);
        $this->assertEquals('5', (string)$subRange);
    }

    public function testOverlapsPartialLeft()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(1, 7);
        $this->assertTrue($subRange1->overlaps($subRange2));
    }

    public function testOverlapsPartialRight()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(8, 15);
        $this->assertTrue($subRange1->overlaps($subRange2));
    }

    public function testOverlapsFullyInside()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(6, 9);
        $this->assertTrue($subRange1->overlaps($subRange2));
    }

    public function testOverlapsFullyEncloses()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(1, 15);
        $this->assertTrue($subRange1->overlaps($subRange2));
    }

    public function testOverlapsExactBounds()
    {
        $subRange1 = new SubRange(5, 10);

        $subRangeLeft = new SubRange(1, 5);
        $this->assertTrue($subRange1->overlaps($subRangeLeft));

        $subRangeRight = new SubRange(10, 15);
        $this->assertTrue($subRange1->overlaps($subRangeRight));
    }

    public function testOverlapsNoOverlapLeft()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(1, 4);
        $this->assertFalse($subRange1->overlaps($subRange2));
    }

    public function testOverlapsNoOverlapRight()
    {
        $subRange1 = new SubRange(5, 10);
        $subRange2 = new SubRange(11, 15);
        $this->assertFalse($subRange1->overlaps($subRange2));
    }

    public function testAddDoesNotTouch()
    {
        $subRange1 = new SubRange(1, 5);
        $subRange2 = new SubRange(7, 10);
        $this->assertNull($subRange1->add($subRange2));
    }

    public function testSubtractDoesNotOverlap()
    {
        $subRange1 = new SubRange(1, 5);
        $subRange2 = new SubRange(7, 10);
        $this->assertNull($subRange1->subtract($subRange2));
    }
}
