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

    public function count()
    {
        return $this->length;
    }

    protected function updateLength()
    {
        $this->length = array_reduce($this->ranges, function ($previous, $range) {
            return $previous + $range->length;
        });
    }

    protected function addSubRange($subRange)
    {
        $newRanges = [];
        $num = 0;

        while ($num < count($this->ranges) && !$subRange->touches($this->ranges[$num])) {
            $newRanges[] = clone($this->ranges[$num]);
            $num++;
        }

        while ($num < count($this->ranges) && $subRange->touches($this->ranges[$num])) {
            $subRange = $subRange->add($this->ranges[$num]);
            $num++;
        }

        $newRanges[] = $subRange;

        while ($num < count($this->ranges)) {
            $newRanges[] = clone($this->ranges[$num]);
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
        } else if ($rangeA instanceof DRange\SubRange) {
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

        while ($num < count($this->ranges) && !$subRange->overlaps($this->ranges[$num])) {
            $newRanges[] = clone($this->ranges[$num]);
            $num++;
        }

        while ($num < count($this->ranges) && $subRange->overlaps($this->ranges[$num])) {
            $newRanges = array_merge($newRanges, $this->ranges[$num]->subtract($subRange));
            $num++;
        }

        while ($num < count($this->ranges)) {
            $newRanges[] = clone($this->ranges[$num]);
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
        } else if ($rangeA instanceof DRange\SubRange) {
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

        while ($num < count($this->ranges) && $this->ranges[$num]->length <= $index) {
            $index -= $this->ranges[$num]->length;
            $num++;
        }

        if ($num >= count($this->ranges)) {
            return null;
        }

        return $this->ranges[$num]->low + $index;
    }
}
