# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-11-21

### Added

- `DRange::__clone()` performs a deep copy, so mutating a cloned `DRange`
  (or the `SubRange` objects inside it) never affects the original.

### Changed

- `SubRange` now uses typed properties (`int $low`, `int $high`, `int $length`)
  and its constructor requires `int` arguments. Non-numeric input that was
  previously coerced silently now raises a `TypeError`.
- Added parameter and return type declarations to `SubRange::overlaps()`,
  `touches()`, `add()`, `subtract()` and `__toString()`, and to
  `DRange::count()`.
- `SubRange::add()` and `SubRange::subtract()` now explicitly return `null`
  when the ranges do not touch / overlap (previously an implicit `null`).
- Removed unnecessary cloning of untouched sub-ranges in `DRange::add()` and
  `DRange::subtract()`, reducing allocations.

### Fixed

- `count()` on an empty `DRange` returned `null` instead of `0`.
- `DRange` stored the caller's `SubRange` instance passed to `add()`, so later
  changes to that object leaked into the range.
- Cloning a `DRange` shared its `SubRange` objects with the original. The
  ineffective `SubRange::__clone()` was removed.

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
