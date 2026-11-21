<?php
namespace jrdev\DRange;

use UnexpectedValueException;

readonly class SubRange
{
    public int $low;
    public int $high;
    public int $length;

    public function __construct(int $low, int $high)
    {
        if ($low > $high) {
            throw new UnexpectedValueException("This range is not acceptable: {$low} - {$high}");
        }

        $this->low = $low;
        $this->high = $high;
        $this->length = 1 + $this->high - $this->low;
    }

    public function __toString(): string
    {
        if ($this->low === $this->high) {
            return strval($this->low);
        }

        return $this->low . '-' . $this->high;
    }

    public function overlaps(SubRange $range): bool
    {
        return !($this->high < $range->low || $this->low > $range->high);
    }

    public function touches(SubRange $range): bool
    {
        return !($this->high + 1 < $range->low || $this->low - 1 > $range->high);
    }

    public function add(SubRange $range): ?SubRange
    {
        if ($this->touches($range)) {
            return new SubRange(min($this->low, $range->low), max($this->high, $range->high));
        }

        return null;
    }

    public function subtract(SubRange $range): ?array
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

        return null;
    }
}
