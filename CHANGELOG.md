# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [5.0.0-alpha4](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha3...5.0.0-alpha4)

- [Exceptions] Added base `UserFacingException` and `NotFoundException`.

## [5.0.0-alpha3](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha2...5.0.0-alpha3)

- [Sprunje] Allow string for `size` and `page` option in `applyPagination`. Fix issue with Sprunje when `$request->getQueryParams()` is passed directly as Sprunje options. 
- Fix Array Cache store for testing
- Add PHP 8.2 to test suite

## [5.0.0-alpha2](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha1...5.0.0-alpha2)

- [Model] Fix `Call to a member function connection() on null` issue with model query builder when no ConnectionResolver was available due to Eloquent not being booted yet.
