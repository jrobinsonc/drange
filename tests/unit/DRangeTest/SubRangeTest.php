<?php
namespace jrdev\DRangeTest;

use \jrdev\DRange\SubRange;

class SubRangeTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \jrdev\UnitTester
     */
    protected $tester;

    public function testInstanciate()
    {
        $subRange = new SubRange(5, 7);
        $this->assertEquals('5-7', $subRange);

        $exception = false;

        try {
            $subRange = new SubRange(7, 5);
        } catch (\UnexpectedValueException $exception) {
        }

        $this->assertInstanceOf('UnexpectedValueException', $exception);
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
}
