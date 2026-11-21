<?php
namespace jrdev;

class DRange implements \Countable
{
    protected $ranges;
    protected $length;
    private array $cumulativeLengths = [];

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
        $total = 0;
        $this->cumulativeLengths = [];

        foreach ($this->ranges as $range) {
            $total += $range->length;
            $this->cumulativeLengths[] = $total;
        }

        $this->length = $total;
    }

    private function toSubRanges($rangeA, $rangeB = null): iterable
    {
        if ($rangeA instanceof DRange) {
            return $rangeA->ranges;
        }

        if ($rangeA instanceof DRange\SubRange) {
            return [$rangeA];
        }

        if ($rangeB === null) {
            $rangeB = $rangeA;
        }

        return [new DRange\SubRange($rangeA, $rangeB)];
    }

    private function appendMerged(array &$merged, DRange\SubRange $subRange): void
    {
        $lastIndex = count($merged) - 1;

        if ($lastIndex >= 0 && $merged[$lastIndex]->touches($subRange)) {
            $merged[$lastIndex] = $merged[$lastIndex]->add($subRange);
            return;
        }

        $merged[] = $subRange;
    }

    public function add($rangeA, $rangeB = null)
    {
        $merged = [];
        $index = 0;
        $rangesCount = count($this->ranges);

        foreach ($this->toSubRanges($rangeA, $rangeB) as $otherRange) {
            while ($index < $rangesCount && $this->ranges[$index]->low <= $otherRange->low) {
                $this->appendMerged($merged, $this->ranges[$index]);
                $index++;
            }

            $this->appendMerged($merged, $otherRange);
        }

        while ($index < $rangesCount) {
            $this->appendMerged($merged, $this->ranges[$index]);
            $index++;
        }

        $this->ranges = $merged;
        $this->updateLength();

        return $this;
    }

    private function subtractAndEmitLeft(
        DRange\SubRange $current,
        DRange\SubRange $otherRange,
        array &$result
    ): ?DRange\SubRange {
        $remainder = null;

        foreach ($current->subtract($otherRange) as $piece) {
            if ($piece->high < $otherRange->low) {
                $result[] = $piece;
                continue;
            }

            $remainder = $piece;
        }

        return $remainder;
    }

    public function subtract($rangeA, $rangeB = null)
    {
        $result = [];
        $index = 0;
        $current = $this->ranges[$index++] ?? null;

        foreach ($this->toSubRanges($rangeA, $rangeB) as $otherRange) {
            while ($current !== null) {
                if ($current->high < $otherRange->low) {
                    $result[] = $current;
                    $current = $this->ranges[$index++] ?? null;
                    continue;
                }

                if ($otherRange->high < $current->low) {
                    break;
                }

                $current = $this->subtractAndEmitLeft($current, $otherRange, $result);

                if ($current !== null) {
                    break;
                }

                $current = $this->ranges[$index++] ?? null;
            }
        }

        while ($current !== null) {
            $result[] = $current;
            $current = $this->ranges[$index++] ?? null;
        }

        $this->ranges = $result;
        $this->updateLength();

        return $this;
    }

    public function index($index)
    {
        if ($index < 0 || $index >= $this->length) {
            return null;
        }

        $low = 0;
        $high = count($this->ranges) - 1;

        while ($low < $high) {
            $mid = intdiv($low + $high, 2);

            if ($this->cumulativeLengths[$mid] <= $index) {
                $low = $mid + 1;
            } else {
                $high = $mid;
            }
        }

        $range = $this->ranges[$low];
        $offset = $this->cumulativeLengths[$low] - $range->length;

        return $range->low + ($index - $offset);
    }

    public function indexOf($value): ?int
    {
        $low = 0;
        $high = count($this->ranges) - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            $range = $this->ranges[$mid];

            if ($value < $range->low) {
                $high = $mid - 1;
                continue;
            }

            if ($value > $range->high) {
                $low = $mid + 1;
                continue;
            }

            $offset = $this->cumulativeLengths[$mid] - $range->length;

            return $offset + ($value - $range->low);
        }

        return null;
    }

    public function intersect($rangeA, $rangeB = null)
    {
        $result = new DRange();
        $index = 0;
        $current = $this->ranges[$index++] ?? null;

        foreach ($this->toSubRanges($rangeA, $rangeB) as $otherRange) {
            while ($current !== null) {
                if ($current->high < $otherRange->low) {
                    $current = $this->ranges[$index++] ?? null;
                    continue;
                }

                if ($otherRange->high < $current->low) {
                    break;
                }

                $result->ranges[] = new DRange\SubRange(
                    max($current->low, $otherRange->low),
                    min($current->high, $otherRange->high)
                );

                if ($current->high <= $otherRange->high) {
                    $current = $this->ranges[$index++] ?? null;
                    continue;
                }

                break;
            }
        }

        $result->updateLength();

        return $result;
    }
}
