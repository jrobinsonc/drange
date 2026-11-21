# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-11-21

### Added

- `DRange::indexOf($value)` returns the position of an integer within the
  range (the inverse of `index()`), or `null` if the value is not in it.

### Changed

- `SubRange` is now a `readonly` class with typed properties (`int $low`,
  `int $high`, `int $length`), and its constructor requires `int` arguments.
  Non-numeric input that was previously coerced silently now raises a
  `TypeError`, and assigning to a `SubRange` property after construction now
  raises an `Error`. The constructor validates `low <= high` before assigning.
- Added parameter and return type declarations to `SubRange::overlaps()`,
  `touches()`, `add()`, `subtract()` and `__toString()`, and to
  `DRange::count()`.
- `SubRange::add()` and `SubRange::subtract()` now explicitly return `null`
  when the ranges do not touch / overlap (previously an implicit `null`).
- `DRange::add()` and `DRange::subtract()` now run in a single linear pass
  over both ranges instead of processing each incoming sub-range separately.
- `DRange::intersect()` now uses a linear two-pointer walk instead of
  comparing every pair of sub-ranges.
- `DRange::index()` now uses a binary search over cumulative lengths.
- Removed the protected `DRange::addSubRange()` and
  `DRange::subtractSubRange()` helpers, and the `SubRange::__clone()` method,
  which is no longer needed now that `SubRange` is immutable. Subclasses of
  `DRange` that called these helpers must use `add()` / `subtract()` instead.
- CI now also tests PHP 8.6 and nightly; Dependabot keeps Composer and GitHub
  Actions dependencies up to date.

### Fixed

- `count()` on an empty `DRange` returned `null` instead of `0`.
- Because `SubRange` was mutable and `DRange` stored the caller's instances
  (and shared them between clones), changing a `SubRange` after passing it to
  a `DRange`, or mutating a cloned `DRange`'s sub-ranges, leaked into the
  original. `SubRange` is now immutable, so this can no longer happen.

## [1.1.0] - 2026-05-06

### Added

- `DRange::intersect()` returns a new range containing only the integers
  present in both ranges. Accepts an integer, a `low, high` pair, a `SubRange`
  or another `DRange`.

### Changed

- **Minimum PHP version raised to 8.2** (was 5.4).
- Updated all development dependencies to their latest versions.
- Rewrote the README with full API documentation and use cases.
- Replaced Travis CI and Scrutinizer with GitHub Actions, testing PHP 8.2–8.5.
- Added a CaptainHook pre-push hook that runs `composer test`.

## [1.0.1] - 2017-07-02

### Changed

- Improved test coverage.
- Confirmed HHVM support in CI.

## [1.0.0] - 2017-07-02

### Added

- Initial release: `DRange` and `SubRange` with `add()`, `subtract()`,
  `index()`, `count()` and string conversion, plus automatic merging and
  splitting of sub-ranges.

[Unreleased]: https://github.com/jrobinsonc/drange/compare/v1.1.0...HEAD
[1.2.0]: https://github.com/jrobinsonc/drange/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/jrobinsonc/drange/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/jrobinsonc/drange/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/jrobinsonc/drange/releases/tag/v1.0.0
