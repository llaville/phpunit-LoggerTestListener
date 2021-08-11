# Changelog
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/),
using the [Keep a CHANGELOG](http://keepachangelog.com) principles.

## [Unreleased]

### Added

- `CHANGELOG.md` file to follow changes to this project

### Changed

- drop PHP 7.1 support
- drop Monolog 1.x support

## [2.0.0] - 2019-01-05

### Changed

- drop PHP 5 support

## [1.7.0] - 2018-10-01

### Changed

- [fb5ae20](https://github.com/llaville/phpunit-LoggerTestListener/commit/fb5ae201e67e379b490c205be662f6470b352c0e) : make it PHPUnit 6 compatible

## [1.6.0] - 2015-10-23

### Changed

- [9ccad51](https://github.com/llaville/phpunit-LoggerTestListener/commit/9ccad51e09a0750dddcbc4f39026f18c201efbc8) : add PHPUnit trace context in log records

## [1.5.0] - 2015-05-04

### Changed

- [7ad1d57](https://github.com/llaville/phpunit-LoggerTestListener/commit/7ad1d57a5f51490d09e2395d15d791c469353264) : make it `PHPUnit_TextUI_ResultPrinter` compatible

## [1.4.0] - 2015-04-21

### Changed

- [b977adf](https://github.com/llaville/phpunit-LoggerTestListener/commit/b977adf04e73706d72bf2c3e96189839e1059be2) : advanced filtering strategies for Monolog uses `bartlett/monolog-callbackfilterhandler`

## [1.3.0] - 2015-04-13

### Added

- [d06710e](https://github.com/llaville/phpunit-LoggerTestListener/commit/d06710e6906bebc3dd86c42e652bf1db41ce02b2) : add trait for classes unable to extend AbstractLoggerTestListener

### Changed

- [5f0e01b](https://github.com/llaville/phpunit-LoggerTestListener/commit/5f0e01b2b40342b65fe3d85ff8dd02c8ea5fa634) : CallbackFilterHandler superseded to AdvancedFilterHandler

## [1.2.0] - 2015-04-07

### Changed

- [34dc4d0](https://github.com/llaville/phpunit-LoggerTestListener/commit/34dc4d06a0ff2b216b1f67ec4d23e04f85af4bd7) : add intermediate suite stats in context
- [0b1aa63](https://github.com/llaville/phpunit-LoggerTestListener/commit/0b1aa63ede72dd98003a3a5c298417474ba76225) : add log context for easy reuse

## [1.1.0] - 2014-09-17

### Added

- filter feature is handled by an [AdvancedFilterHandler](https://github.com/llaville/phpunit-LoggerTestListener/blob/c5e5d5541fb311e872bf173638e21a12447485ee/extra/AdvancedFilterHandler.php)

## [1.0.0] - 2014-08-26

Initial version

[unreleased]: https://github.com/llaville/phpunit-LoggerTestListener/compare/2.0.0...HEAD
[2.0.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/2.0.0RC1...2.0.0
[1.7.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.5.0...1.6.0
[1.6.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/llaville/phpunit-LoggerTestListener/compare/1.0.0...1.1.0
