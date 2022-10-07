# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Fixed

### Removed

## [0.8.0] - 2022 10 07

### Added

- Added single email verification endpoint

### Changed

### Fixed

### Removed

## [0.7.0] - 2022 09 28

### Added

- Allow users to add in-reply-to header
- Added single email verification endpoint

### Changed

### Fixed

- PHP 8.1 Deprecation errors https://github.com/mailersend/mailersend-php/pull/63 https://github.com/mailersend/mailersend-php/pull/64

### Removed

## [0.6.0] - 2022 07 28

### Added

- Email Verification
- SMS endpoints
- New URL generator

### Changed

### Fixed

### Removed

## [0.5.0] - 2022 02 01

### Added

- Add schedule messages endpoints
- Add precedence bulk header

### Changed

- Moving to proper semver, will bump the MINOR version with non hotfix updates
- Add the `domain_id` to the delete endpoint of suppression lists

### Fixed

### Removed

## [0.4.1] - 2021 12 07

### Added

- Add inbound routes endpoints
- Add Domain endpoint
- Get DNS Records endpoint

### Changed

### Fixed

- Add `reply_to` option to README.md

### Removed

## [0.4.0] - 2021 10 13

### Added

- Add template endpoints
- Add the recipient/suppression lists endpoints
- Add domain verification endpoint
- Bulk email endpoints

## [0.3.1] - 2021 06 16

### Changed

- Updated constant to identify SDK version

## [0.3.0] - 2021 06 16

### Added

- Add `GET`, `PUT` and `DELETE` methods to `HttpLayer` class
- Messages API, listing resources, and showing a specific one.
- Webhook, get, find, create, update, and delete endpoints.
- Token create, update, delete endpoints.
- Activity API: list all activities endpoint.
- Analytics API: activities data by date, opens by country, opens by user-agent name and opens by reading environment.
- Domain API: list domains, get a single domain, delete domain, get recipients for a domain and update domain settings.
- Recipients endpoint, List, Find, and Delete.

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

[Unreleased]: https://github.com/mailersend/mailersend-php/compare/v0.8.0...HEAD
[0.8.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.8.0
[0.7.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.7.0
[0.6.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.6.0
[0.5.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.5.0
[0.4.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.4.1
[0.4.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.4.0
[0.3.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.3.1
[0.3.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.3.0
[0.2.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.2.1
[0.2]: https://github.com/mailersend/mailersend-php/releases/tag/v0.2
[0.1.1]: https://github.com/mailersend/mailersend-php/releases/tag/v0.1.1
[0.1.0]: https://github.com/mailersend/mailersend-php/releases/tag/v0.1.0
