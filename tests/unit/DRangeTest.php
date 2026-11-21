<?php
namespace jrdev;

use jrdev\DRange\SubRange;
use ReflectionProperty;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
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
        $empty = new DRange();
        $this->assertNull($empty->index(0));
        $this->assertNull($empty->index(-1));

        $drange = new DRange(0, 9);
        $drange->add(20, 29);
        $drange->add(35);
        $drange->add(40, 49);

        $this->assertNull($drange->index(-1));
        $this->assertEquals(0, $drange->index(0));
        $this->assertEquals(5, $drange->index(5));
        $this->assertEquals(9, $drange->index(9));
        $this->assertEquals(20, $drange->index(10));
        $this->assertEquals(25, $drange->index(15));
        $this->assertEquals(29, $drange->index(19));
        $this->assertEquals(35, $drange->index(20));
        $this->assertEquals(40, $drange->index(21));
        $this->assertEquals(45, $drange->index(26));
        $this->assertEquals(49, $drange->index(30));
        $this->assertNull($drange->index(31));
        $this->assertNull($drange->index(55));
        $this->assertEquals(31, count($drange));

        $drange->subtract(20, 29);
        $this->assertEquals(35, $drange->index(10));
        $this->assertEquals(40, $drange->index(11));
        $this->assertNull($drange->index(21));
    }

    public function testIndexOf()
    {
        $empty = new DRange();
        $this->assertNull($empty->indexOf(0));
        $this->assertNull($empty->indexOf(-5));

        $drange = new DRange(0, 9);
        $drange->add(20, 29);
        $drange->add(35);
        $drange->add(40, 49);

        // Before first sub-range
        $this->assertNull($drange->indexOf(-1));
        $this->assertNull($drange->indexOf(-10));

        // First sub-range [0-9] -> positions 0..9
        $this->assertSame(0, $drange->indexOf(0));
        $this->assertSame(5, $drange->indexOf(5));
        $this->assertSame(9, $drange->indexOf(9));

        // Gap between 9 and 20
        $this->assertNull($drange->indexOf(10));
        $this->assertNull($drange->indexOf(15));
        $this->assertNull($drange->indexOf(19));

        // Second sub-range [20-29] -> positions 10..19
        $this->assertSame(10, $drange->indexOf(20));
        $this->assertSame(15, $drange->indexOf(25));
        $this->assertSame(19, $drange->indexOf(29));

        // Gap between 29 and 35
        $this->assertNull($drange->indexOf(30));
        $this->assertNull($drange->indexOf(34));

        // Single-value sub-range [35] -> position 20
        $this->assertSame(20, $drange->indexOf(35));

        // Gap between 35 and 40
        $this->assertNull($drange->indexOf(36));
        $this->assertNull($drange->indexOf(39));

        // Fourth sub-range [40-49] -> positions 21..30
        $this->assertSame(21, $drange->indexOf(40));
        $this->assertSame(26, $drange->indexOf(45));
        $this->assertSame(30, $drange->indexOf(49));

        // After last sub-range
        $this->assertNull($drange->indexOf(50));
        $this->assertNull($drange->indexOf(100));

        // Round-trip consistency with index()
        $totalLength = count($drange);
        for ($position = 0; $position < $totalLength; $position++) {
            $this->assertSame($position, $drange->indexOf($drange->index($position)));
        }

        // After subtract mutation
        $drange->subtract(20, 29);
        $this->assertNull($drange->indexOf(20));
        $this->assertNull($drange->indexOf(25));
        $this->assertSame(10, $drange->indexOf(35));
        $this->assertSame(11, $drange->indexOf(40));
        $this->assertSame(20, $drange->indexOf(49));

        // Negative integer sub-ranges
        $negative = (new DRange(-10, -5))->add(0, 3);
        $this->assertNull($negative->indexOf(-11));
        $this->assertSame(0, $negative->indexOf(-10));
        $this->assertSame(5, $negative->indexOf(-5));
        $this->assertNull($negative->indexOf(-4));
        $this->assertSame(6, $negative->indexOf(0));
        $this->assertSame(9, $negative->indexOf(3));
        $this->assertNull($negative->indexOf(4));
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

    public function testIntersectMultiSubRangeDRanges()
    {
        // Does not mutate receiver or operand
        $drange1 = (new DRange(1, 5))->add(10, 15);
        $drange2 = (new DRange(3, 12))->add(20, 25);
        $result = $drange1->intersect($drange2);
        $this->assertNotSame($drange1, $result);
        $this->assertNotSame($drange2, $result);
        $this->assertEquals('[ 1-5, 10-15 ]', (string)$drange1);
        $this->assertEquals(11, count($drange1));
        $this->assertEquals('[ 3-12, 20-25 ]', (string)$drange2);
        $this->assertEquals(16, count($drange2));
        $this->assertEquals('[ 3-5, 10-12 ]', (string)$result);
        $this->assertEquals(6, count($result));

        // Multiple partial overlaps on both sides (interleaved)
        $interleavedA = (new DRange(1, 6))->add(10, 16)->add(20, 26);
        $interleavedB = (new DRange(4, 12))->add(14, 22);
        $interleavedResult = $interleavedA->intersect($interleavedB);
        $this->assertEquals('[ 4-6, 10-12, 14-16, 20-22 ]', (string)$interleavedResult);
        $this->assertEquals(12, count($interleavedResult));

        // One range spanning several on the other side (both directions)
        $spanning = new DRange(2, 28);
        $multiple = (new DRange(1, 5))->add(10, 15)->add(20, 25)->add(27, 30);
        $spanResult1 = $spanning->intersect($multiple);
        $this->assertEquals('[ 2-5, 10-15, 20-25, 27-28 ]', (string)$spanResult1);
        $this->assertEquals(18, count($spanResult1));

        $spanResult2 = $multiple->intersect($spanning);
        $this->assertEquals('[ 2-5, 10-15, 20-25, 27-28 ]', (string)$spanResult2);
        $this->assertEquals(18, count($spanResult2));

        // Ranges meeting at a single shared integer
        $touchingA = (new DRange(1, 5))->add(10, 15);
        $touchingB = (new DRange(5, 10))->add(15, 20);
        $touchingResult = $touchingA->intersect($touchingB);
        $this->assertEquals('[ 5, 10, 15 ]', (string)$touchingResult);
        $this->assertEquals(3, count($touchingResult));

        // Adjacent ranges without overlap (off by 1)
        $adjacentA = (new DRange(1, 4))->add(10, 14);
        $adjacentB = (new DRange(5, 9))->add(15, 20);
        $adjacentResult = $adjacentA->intersect($adjacentB);
        $this->assertEquals('[  ]', (string)$adjacentResult);
        $this->assertEquals(0, count($adjacentResult));

        // Empty operand on either side
        $nonEmpty = (new DRange(1, 5))->add(10, 15);
        $empty = new DRange();

        $emptyResult1 = $nonEmpty->intersect($empty);
        $this->assertEquals('[  ]', (string)$emptyResult1);
        $this->assertEquals(0, count($emptyResult1));

        $emptyResult2 = $empty->intersect($nonEmpty);
        $this->assertEquals('[  ]', (string)$emptyResult2);
        $this->assertEquals(0, count($emptyResult2));
    }

    private function storedRanges(DRange $drange): array
    {
        return (new ReflectionProperty(DRange::class, 'ranges'))->getValue($drange);
    }
}
