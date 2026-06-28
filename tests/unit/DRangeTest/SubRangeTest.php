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

    public function testAddTouches()
    {
        $subRange1 = new SubRange(1, 5);
        $subRange2 = new SubRange(6, 10);

        $added = $subRange1->add($subRange2);
        $this->assertInstanceOf(SubRange::class, $added);
        $this->assertEquals(1, $added->low);
        $this->assertEquals(10, $added->high);

        // Test commutative property (adding lower to higher)
        $addedReverse = $subRange2->add($subRange1);
        $this->assertInstanceOf(SubRange::class, $addedReverse);
        $this->assertEquals(1, $addedReverse->low);
        $this->assertEquals(10, $addedReverse->high);
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
