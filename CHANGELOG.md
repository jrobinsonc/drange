# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-05-06

### Added
- **Intersect method**: New `DRange::intersect()` method for computing intersections between ranges
  - Supports intersection with integers, contiguous ranges, `SubRange` instances, and other `DRange` objects
  - Returns a new `DRange` containing only integers present in both ranges

### Changed
- **PHP version requirement**: Updated minimum PHP version to 8.2 (from 8.0)
- **CI improvements**: Updated GitHub Actions to test PHP versions 8.2–8.5 (previously 8.0–8.5)
- **Documentation**: Comprehensive README rewrite with full API documentation and real-world use cases

### Improved
- Enhanced `composer.json` description and keywords
- Added test status badge to README
- Updated all dev dependencies to latest versions
- Improved test coverage

### Removed
- Removed legacy Travis CI configuration
- Removed Scrutinizer configuration
- Cleaned up platform-specific Composer configurations

### Fixed
- Fixed CI platform configuration for PHP 8.2 development dependencies

## [1.0.1] - Earlier version

Initial stable release
