# DRange

[![Tests](https://github.com/jrobinsonc/drange/actions/workflows/tests.yml/badge.svg)](https://github.com/jrobinsonc/drange/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/jrdev/drange)](https://packagist.org/packages/jrdev/drange)
[![PHP](https://img.shields.io/badge/php-%3E%3D8.2-8892bf)](https://www.php.net/supported-versions.php)
[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/jrobinsonc/drange/blob/master/LICENSE)

DRange is a PHP library for managing **discontinuous (non-contiguous) ranges of integers**. Rather than storing every number individually, it holds a compact, sorted list of contiguous sub-ranges — automatically merging adjacent ranges when you add numbers and splitting them when you subtract. The result is an expressive, memory-efficient structure for any problem that involves gaps in sequences.

## Features

- Add, subtract, or intersect individual integers, numeric ranges, `SubRange` instances, or entire `DRange` objects
- Automatic merging of adjacent and overlapping ranges on `add()`
- Automatic splitting of ranges on `subtract()`
- Look up the value at a position (`->index($position)`) or the position of a value (`->indexOf($value)`), both by binary search
- Linear-time `add()`, `subtract()` and `intersect()`, however many sub-ranges are involved
- Native PHP `count()` support via `Countable`
- Human-readable string representation (e.g. `[ 1-5, 8-10 ]`)
- Chainable `add()` and `subtract()` calls
- Immutable `SubRange` values, so a cloned `DRange` never shares changes with the original
- Zero runtime dependencies

## Requirements

- PHP >= 8.2
- Composer

## Installation

```bash
composer require jrdev/drange
```

```php
use jrdev\DRange;
use jrdev\DRange\SubRange; // only needed if you use SubRange directly
```

## Quick Start

```php
$drange = new DRange(1, 5);   // [ 1-5 ]
$drange->add(8);               // [ 1-5, 8 ]   gap at 6-7
$drange->add(6, 7);            // [ 1-8 ]       auto-merged
$drange->subtract(1, 3);       // [ 4-8 ]

echo $drange;           // "[ 4-8 ]"
echo count($drange);    // 5
```

## API Reference

### `DRange` (`jrdev\DRange`)

| Method / Usage | Returns | Description |
|---|---|---|
| `new DRange()` | `DRange` | Empty range |
| `new DRange($n)` | `DRange` | Range containing the single integer `$n` |
| `new DRange($low, $high)` | `DRange` | Contiguous range `[$low..$high]` |
| `->add($n)` | `$this` | Add a single integer |
| `->add($low, $high)` | `$this` | Add a contiguous range |
| `->add($drange)` | `$this` | Add all sub-ranges from another `DRange` |
| `->add($subrange)` | `$this` | Add a `SubRange` instance |
| `->subtract($n)` | `$this` | Subtract a single integer |
| `->subtract($low, $high)` | `$this` | Subtract a contiguous range |
| `->subtract($drange)` | `$this` | Subtract all sub-ranges of another `DRange` |
| `->subtract($subrange)` | `$this` | Subtract a `SubRange` instance |
| `->intersect($n)` | `DRange` | New range containing only integers present in both this range and `$n` |
| `->intersect($low, $high)` | `DRange` | Intersection with a contiguous range |
| `->intersect($drange)` | `DRange` | Intersection with another `DRange` |
| `->intersect($subrange)` | `DRange` | Intersection with a `SubRange` instance |
| `->index($position)` | `int\|null` | Value at the 0-based `$position`; `null` if out of bounds |
| `->indexOf($value)` | `int\|null` | 0-based position of `$value`; `null` if the value is not in the range |
| `count($drange)` | `int` | Total number of values across all sub-ranges (`0` when empty) |
| `(string) $drange` | `string` | e.g. `"[ 1-5, 8-10 ]"` |
| `clone $drange` | `DRange` | Independent copy; changes to the clone do not affect the original |

Adjacent ranges (touching but not overlapping) are merged automatically on every `add()` call.

### `SubRange` (`jrdev\DRange\SubRange`)

`SubRange` is a `readonly` class: its properties are set once by the constructor, and assigning to them afterwards throws an `\Error`. Methods that change a range return new `SubRange` objects.

| Member | Type | Description |
|---|---|---|
| `new SubRange(int $low, int $high)` | — | Throws `\UnexpectedValueException` if `$low > $high`, and `\TypeError` for non-integer bounds |
| `->low` | `int` | Lower bound (inclusive) |
| `->high` | `int` | Upper bound (inclusive) |
| `->length` | `int` | `$high - $low + 1` |
| `->overlaps($range)` | `bool` | `true` if the two ranges share at least one value |
| `->touches($range)` | `bool` | `true` if the ranges overlap or are adjacent (no gap between them) |
| `->add($range)` | `SubRange\|null` | Merged range; `null` if the ranges do not touch |
| `->subtract($range)` | `SubRange[]\|null` | 0, 1, or 2 sub-ranges left after removing the overlap; `null` if the ranges do not overlap |
| `(string) $subrange` | `string` | `"low-high"` or just `"n"` for a single-value range |

## Usage Examples

### Method chaining

```php
$drange = (new DRange(1, 10))
    ->subtract(5)
    ->subtract(7);
// [ 1-4, 6, 8-10 ]
```

### Operating on two DRange objects

```php
$allowed = new DRange(1, 65535);

$blocked = new DRange(22);
$blocked->add(23)->add(3306)->add(5432);

$available = clone $allowed;
$available->subtract($blocked);
// [ 1-21, 24-3305, 3307-5431, 5433-65535 ]
// $allowed is still [ 1-65535 ]
```

### Positions and values with `index()` and `indexOf()`

```php
$drange = new DRange(0, 9);   // 10 values: 0–9
$drange->add(20, 29);          // 10 more:    20–29
$drange->add(40, 49);          // 10 more:    40–49

$drange->index(0);   // 0   (1st value)
$drange->index(15);  // 25  (16th value, 6th in second sub-range)
$drange->index(25);  // 45  (26th value, 6th in third sub-range)
$drange->index(55);  // null (out of bounds)

$drange->indexOf(25);  // 15   (the inverse of index())
$drange->indexOf(15);  // null (15 falls in the gap 10–19)
```

### Counting with native `count()`

```php
$drange = new DRange(1, 5);   // 5 values
$drange->add(10, 15);          // 6 more

count($drange); // 11
```

### Computing intersections with `intersect()`

```php
$available = new DRange(1, 10);
$available->add(20, 30);
// [ 1-10, 20-30 ]

$requested = new DRange(5, 25);

$overlap = $available->intersect($requested);
// [ 5-10, 20-25 ]

count($overlap); // 12
```

### Using `SubRange` directly

```php
use jrdev\DRange\SubRange;

$drange = new DRange(1, 10);
$drange->subtract(new SubRange(4, 6));
// [ 1-3, 7-10 ]
```

## Real-World Use Cases

- **Network port management** — Represent firewall allowlists or blocklists as unions of port ranges; subtract blocked ranges from the full `0–65535` range to derive what is open.
- **HTTP range requests** — Track which byte offsets of a large file have been downloaded; call `subtract()` as each chunk arrives and `count()` to know how many bytes remain.
- **Appointment and booking systems** — Represent availability as numeric time ranges (minutes since midnight, Unix timestamps); subtract booked slots to see what is still open.
- **Batch job progress tracking** — Record which database record ID ranges have been processed; use `index()` to resume from an arbitrary position without scanning the full set.
- **Pagination and lazy loading** — Track which page or item index ranges have already been fetched from an API; avoid re-requesting pages that fall inside an already-loaded range.
- **Seat allocation** — Model auditorium or transit rows as numeric ranges; subtract reserved seats and inspect the remaining range to find the next available contiguous block.
- **IP address management** — Convert IPv4 addresses to 32-bit integers and use `DRange` to track allocated and free address blocks without needing a dedicated CIDR library.

## Versioning

DRange follows [Semantic Versioning](https://semver.org). See [CHANGELOG.md](CHANGELOG.md) for the changes in each release, and [RELEASING.md](RELEASING.md) for how releases are cut.

## License

Licensed under the [MIT licence](https://github.com/jrobinsonc/drange/blob/master/LICENSE).
