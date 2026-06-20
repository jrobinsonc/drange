<?php
namespace jrdev;

class DRange implements \Countable
{
    protected $ranges;
    protected $length;

    public function __construct($rangeA = null, $rangeB = null)
    {
        $this->ranges = [];
        $this->length = 0;

        if ($rangeA !== null) {
            $this->add($rangeA, $rangeB);
        }
    }

    public function __toString()
    {
        return '[ ' . implode(', ', $this->ranges) . ' ]';
    }

    public function count(): int
    {
        return $this->length;
    }

    protected function updateLength()
    {
        $this->length = array_reduce($this->ranges, function ($previous, $range) {
            return $previous + $range->length;
        }, 0);
    }

    protected function addSubRange($subRange)
    {
        $newRanges = [];
        $num = 0;
        $rangesCount = count($this->ranges);

        while ($num < $rangesCount && !$subRange->touches($this->ranges[$num])) {
            $newRanges[] = $this->ranges[$num];
            $num++;
        }

        while ($num < $rangesCount && $subRange->touches($this->ranges[$num])) {
            $subRange = $subRange->add($this->ranges[$num]);
            $num++;
        }

        $newRanges[] = $subRange;

        while ($num < $rangesCount) {
            $newRanges[] = $this->ranges[$num];
            $num++;
        }

        $this->ranges = $newRanges;
        $this->updateLength();
    }

    public function add($rangeA, $rangeB = null)
    {
        if ($rangeA instanceof DRange) {
            foreach ($rangeA->ranges as $item) {
                $this->addSubRange($item);
            }
        } elseif ($rangeA instanceof DRange\SubRange) {
            $this->addSubRange($rangeA);
        } else {
            if ($rangeB === null) {
                $rangeB = $rangeA;
            }

            $this->addSubRange(new DRange\SubRange($rangeA, $rangeB));
        }

        return $this;
    }

    protected function subtractSubRange($subRange)
    {
        $newRanges = [];
        $num = 0;
        $rangesCount = count($this->ranges);

        while ($num < $rangesCount && !$subRange->overlaps($this->ranges[$num])) {
            $newRanges[] = $this->ranges[$num];
            $num++;
        }

        while ($num < $rangesCount && $subRange->overlaps($this->ranges[$num])) {
            $newRanges = array_merge($newRanges, $this->ranges[$num]->subtract($subRange));
            $num++;
        }

        while ($num < $rangesCount) {
            $newRanges[] = $this->ranges[$num];
            $num++;
        }

        $this->ranges = $newRanges;
        $this->updateLength();
    }

    public function subtract($rangeA, $rangeB = null)
    {
        if ($rangeA instanceof DRange) {
            foreach ($rangeA->ranges as $item) {
                $this->subtractSubRange($item);
            }
        } elseif ($rangeA instanceof DRange\SubRange) {
            $this->subtractSubRange($rangeA);
        } else {
            if ($rangeB === null) {
                $rangeB = $rangeA;
            }

            $this->subtractSubRange(new DRange\SubRange($rangeA, $rangeB));
        }

        return $this;
    }

    public function index($index)
    {
        $num = 0;
        $rangesCount = count($this->ranges);

        while ($num < $rangesCount && $this->ranges[$num]->length <= $index) {
            $index -= $this->ranges[$num]->length;
            $num++;
        }

        if ($num >= $rangesCount) {
            return null;
        }

        return $this->ranges[$num]->low + $index;
    }

    public function intersect($rangeA, $rangeB = null)
    {
        $result = new DRange();

        if ($rangeA instanceof DRange) {
            $other = $rangeA;
        } elseif ($rangeA instanceof DRange\SubRange) {
            $other = new DRange($rangeA);
        } else {
            if ($rangeB === null) {
                $rangeB = $rangeA;
            }
            $other = new DRange($rangeA, $rangeB);
        }

        foreach ($this->ranges as $subRange) {
            foreach ($other->ranges as $otherSubRange) {
                if ($subRange->overlaps($otherSubRange)) {
                    $result->addSubRange(new DRange\SubRange(
                        max($subRange->low, $otherSubRange->low),
                        min($subRange->high, $otherSubRange->high)
                    ));
                }
            }
        }

        return $result;
    }
}
