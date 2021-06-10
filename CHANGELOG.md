# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add `GET`, `PUT` and `DELETE` methods to `HttpLayer` class
- Messages API, listing resources, and showing a specific one.

### Changed

### Fixed

### Removed

## [0.2.1] - 2021 04 23

### Added

- `Debugging validation errors` to GUIDE.md

### Changed

- `master` to `main`

### Fixed

- Some problems with tags and `main`
- GUIDE.md fixes

## [0.2] - 2021 01 28

### Added

- PHP 8 support
- Github Actions testing for multiple versions of PHP

### Changed

- Guzzle 6 to Guzzle 7 in `require-dev`.
- `tightenco/collect` version constraint update.
- Suggest `php-http/guzzle7-adapter` instead of `php-http/guzzle6-adapter` for install instructions.

## [0.1.1] - 2020 09 01

### Changed

- `collect` helper changed to `Collection` class
- `from` and `subject` are not mandatory if `template_id` is set

## [0.1.0]

### Added

- PSR-7 and PSR-18 based HTTP adapter
- `POST email/send` endpoint support
- Helpers for recipients, variables & attachments
- PHPUnit tests
- Documentation

[Unreleased]: https://github.com/mailersend/mailersend-php/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.2.1
[0.2]: https://github.com/mailersend/mailersend-php/releases/tag/v0.2
[0.1.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.1.1
[0.1.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.1.0
