# DRange

A PHP library for working with sets of integers stored as ordered, non-touching ranges.

## Language

**DRange**:
A set of integers, kept as an ascending list of sub-ranges that neither overlap nor touch.
_Avoid_: Range set, multi-range

**Sub-range**:
One continuous run of integers inside a DRange, from a low bound to a high bound, both included.
_Avoid_: Segment, interval, chunk

**Value**:
An integer that belongs to a DRange.
_Avoid_: Element, item, number

**Position**:
The 0-based place of a value when every value in the DRange is listed in ascending order. `index()` maps a position to its value; `indexOf()` maps a value to its position.
_Avoid_: Offset, index (as a noun), rank
