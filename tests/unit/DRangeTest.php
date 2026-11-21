<?php
namespace jrdev;

use jrdev\DRange\SubRange;
use ReflectionProperty;

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

    public function testCloneAddLeavesOriginalUnchanged()
    {
        $drange = new DRange(0, 9);
        $drange2 = clone($drange);

        $drange2->add(10, 15);
        $this->assertEquals('[ 0-9 ]', $drange);
        $this->assertEquals('[ 0-15 ]', $drange2);
    }

    public function testEmptyDRange()
    {
        $drange = new DRange();
        $this->assertEquals('[  ]', (string)$drange);
        $this->assertEquals(0, count($drange));
        $this->assertEquals(0, $drange->count());
    }

    public function testSubtractToEmpty()
    {
        $drange = new DRange(1, 10);
        $drange->subtract(1, 10);
        $this->assertEquals('[  ]', (string)$drange);
        $this->assertEquals(0, count($drange));
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

    public function testCloneSubRangeCannotBeMutated()
    {
        $drange = new DRange(1, 3);
        $drange2 = clone($drange);

        $this->expectException(\Error::class);
        $this->storedRanges($drange2)[0]->low = 2;
    }

    public function testAddedCallerSubRangeCannotBeMutated()
    {
        $subRange = new SubRange(1, 3);
        $drange = new DRange();
        $drange->add($subRange);

        $this->expectException(\Error::class);
        $subRange->low = 2;
    }

    public function testAddedOtherDRangeSubRangeCannotBeMutated()
    {
        $source = new DRange(1, 3);
        $target = new DRange();
        $target->add($source);

        $this->expectException(\Error::class);
        $this->storedRanges($target)[0]->low = 2;
    }

    public function testAddMultiSubRangeDRanges()
    {
        // Disjoint with earlier and interleaved ranges
        $disjoint = (new DRange(10, 15))->add(30, 35);
        $disjointOther = (new DRange(1, 5))->add(20, 25)->add(40, 45);
        $this->assertSame($disjoint, $disjoint->add($disjointOther));
        $this->assertEquals('[ 1-5, 10-15, 20-25, 30-35, 40-45 ]', (string)$disjoint);
        $this->assertEquals(29, count($disjoint));

        // Interleaving overlaps
        $interleaved = (new DRange(1, 5))->add(10, 15)->add(20, 25);
        $interleavedOther = (new DRange(3, 12))->add(18, 22);
        $interleaved->add($interleavedOther);
        $this->assertEquals('[ 1-15, 18-25 ]', (string)$interleaved);
        $this->assertEquals(23, count($interleaved));

        // Touching at boundaries
        $touching = (new DRange(1, 5))->add(10, 15);
        $touchingOther = (new DRange(6, 9))->add(16, 20);
        $touching->add($touchingOther);
        $this->assertEquals('[ 1-20 ]', (string)$touching);
        $this->assertEquals(20, count($touching));

        // Target fully contains added multi-sub-range DRange
        $outer = new DRange(1, 30);
        $inner = (new DRange(5, 10))->add(15, 20);
        $outer->add($inner);
        $this->assertEquals('[ 1-30 ]', (string)$outer);
        $this->assertEquals(30, count($outer));

        // Added DRange fully contains target's multiple sub-ranges
        $inner->add(new DRange(1, 30));
        $this->assertEquals('[ 1-30 ]', (string)$inner);
        $this->assertEquals(30, count($inner));

        // Empty DRange cases
        $nonEmpty = (new DRange(1, 3))->add(7, 9);
        $nonEmpty->add(new DRange());
        $this->assertEquals('[ 1-3, 7-9 ]', (string)$nonEmpty);
        $this->assertEquals(6, count($nonEmpty));

        $empty = new DRange();
        $empty->add($nonEmpty);
        $this->assertEquals('[ 1-3, 7-9 ]', (string)$empty);
        $this->assertEquals(6, count($empty));
    }

    public function testSubtractMultiSubRangeDRanges()
    {
        // Disjoint
        $disjoint = (new DRange(10, 15))->add(25, 30);
        $disjointOther = (new DRange(1, 5))->add(18, 22)->add(35, 40);
        $this->assertSame($disjoint, $disjoint->subtract($disjointOther));
        $this->assertEquals('[ 10-15, 25-30 ]', (string)$disjoint);
        $this->assertEquals(12, count($disjoint));

        // Adjacent boundaries (non-overlapping)
        $adjacent = (new DRange(5, 10))->add(15, 20);
        $adjacentOther = (new DRange(1, 4))->add(11, 14)->add(21, 25);
        $adjacent->subtract($adjacentOther);
        $this->assertEquals('[ 5-10, 15-20 ]', (string)$adjacent);
        $this->assertEquals(12, count($adjacent));

        // Boundary overlap by a single integer
        $boundaryOverlap = (new DRange(5, 10))->add(15, 20);
        $boundaryOther = (new DRange(1, 5))->add(10, 15)->add(20, 25);
        $boundaryOverlap->subtract($boundaryOther);
        $this->assertEquals('[ 6-9, 16-19 ]', (string)$boundaryOverlap);
        $this->assertEquals(8, count($boundaryOverlap));

        // Interleaving overlaps spanning across sub-ranges
        $interleaved = (new DRange(1, 10))->add(15, 25)->add(30, 40);
        $interleavedOther = (new DRange(5, 18))->add(22, 35);
        $interleaved->subtract($interleavedOther);
        $this->assertEquals('[ 1-4, 19-21, 36-40 ]', (string)$interleaved);
        $this->assertEquals(12, count($interleaved));

        // Target fully contains subtracted multi-sub-range DRange
        $outer = new DRange(1, 100);
        $inner = (new DRange(10, 20))->add(30, 40)->add(50, 60);
        $outer->subtract($inner);
        $this->assertEquals('[ 1-9, 21-29, 41-49, 61-100 ]', (string)$outer);
        $this->assertEquals(67, count($outer));

        // Subtracted DRange fully contains target's multiple sub-ranges
        $multiTarget = (new DRange(5, 10))->add(15, 20)->add(25, 30);
        $multiTarget->subtract(new DRange(1, 35));
        $this->assertEquals('[  ]', (string)$multiTarget);
        $this->assertEquals(0, count($multiTarget));

        // Exact match across multiple sub-ranges
        $exact = (new DRange(5, 10))->add(15, 20);
        $exact->subtract((new DRange(5, 10))->add(15, 20));
        $this->assertEquals('[  ]', (string)$exact);
        $this->assertEquals(0, count($exact));

        // Empty DRange cases
        $nonEmpty = (new DRange(1, 3))->add(7, 9);
        $nonEmpty->subtract(new DRange());
        $this->assertEquals('[ 1-3, 7-9 ]', (string)$nonEmpty);
        $this->assertEquals(6, count($nonEmpty));

        $empty = new DRange();
        $empty->subtract($nonEmpty);
        $this->assertEquals('[  ]', (string)$empty);
        $this->assertEquals(0, count($empty));
    }

    private function storedRanges(DRange $drange): array
    {
        return (new ReflectionProperty(DRange::class, 'ranges'))->getValue($drange);
    }
}
