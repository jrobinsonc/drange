<?php
namespace jrdev\DRange;

class SubRange
{
    public $low;
    public $high;
    public $length;

    public function __construct($low, $high)
    {
        $this->low = $low;
        $this->high = $high;
        $this->length = 1 + $this->high - $this->low;

        if ($this->low > $this->high) {
            throw new \UnexpectedValueException("This range is not acceptable: {$this->low} - {$this->high}");
        }
    }

    public function __toString()
    {
        if ($this->low === $this->high) {
            return strval($this->low);
        }

        return $this->low . '-' . $this->high;
    }

    public function __clone()
    {
        return new SubRange($this->low, $this->high);
    }

    public function overlaps($range)
    {
        return !($this->high < $range->low || $this->low > $range->high);
    }

    public function touches($range)
    {
        return !($this->high + 1 < $range->low || $this->low - 1 > $range->high);
    }

    public function add($range)
    {
        if ($this->touches($range)) {
            return new SubRange(min($this->low, $range->low), max($this->high, $range->high));
        }
    }

    public function subtract($range)
    {
        if ($this->overlaps($range)) {
            if ($range->low <= $this->low && $range->high >= $this->high) {
                return [];
            }

            if ($range->low > $this->low && $range->high < $this->high) {
                return [new SubRange($this->low, $range->low - 1), new SubRange($range->high + 1, $this->high)];
            }

            if ($range->low <= $this->low) {
                return [new SubRange($range->high + 1, $this->high)];
            }

            return [new SubRange($this->low, $range->low - 1)];
        }
    }
}
