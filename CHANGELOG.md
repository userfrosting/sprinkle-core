# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [5.1.1](https://github.com/userfrosting/sprinkle-core/compare/5.1.0...5.1.1)
- Fix issue with sprunje using multiple listable fetched from database ([Chat Reference](https://chat.userfrosting.com/channel/support?msg=sgMq8sbAjsCN2ZGXj))

## [5.1.0](https://github.com/userfrosting/sprinkle-core/compare/5.0.1...5.1.0)
- Drop PHP 8.1 support, add PHP 8.3 support
- Update to Laravel 10
- Update to PHPUnit 10
- Update to Monolog 3
- Test against MariaDB [#1238](https://github.com/userfrosting/UserFrosting/issues/1238)
- The different loggers now implement their own interface
- Change sprunje type-hinting, fixing issue with some many-to-many relations
- New Twig function : `config`
- Use our own RouterParser, wrapped around Slim's RouteParser. Allows to add 'fallback' routes when names routes are not found.

### Bakery
- Rework assets building command. This change allows new bakery command to update Npm assets, and eventually allows sprinkles to replace webpack with something else (eg. Vite). The new commands are :
  - `assets:install` : Alias for `npm install`.
  - `assets:update` : Alias for `npm update`.
  - `assets:webpack` : Alias for `npm run dev`, `npm run build` and `npm run watch`, each used to run Webpack Encore.
  - `assets:build` : Aggregator command for building assets. Includes by default `assets:install` and `assets:webpack`. Sub commands can be added to `assets:build` by listening to `AssetsBuildCommandEvent`.
  - The old `webpack` and `build-assets` command are still available, and now alias `assets:build`. `bake` also uses `assets:build` now. 
- New Bakery commands : `serve` & `debug:twig`

## [5.0.1](https://github.com/userfrosting/sprinkle-core/compare/5.0.0...5.0.1)
- Add env for public URI, default back to empty string

## [5.0.0-alpha9](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha8...5.0.0-alpha9)
- Reenable CSRF protection for all routes.
- Update `DebugCommand` : Split command into multiple subcommand and use `DebugCommandEvent` and `DebugVerboseCommandEvent` so custom command can be added to the debug command, like the `bake` command.
- Add `build-assets` as alias to `webpack` command for legacy purpose. 

## [5.0.0-alpha8](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha7...5.0.0-alpha8)
- Update for PHP-DI 7

## [5.0.0-alpha7](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha6...5.0.0-alpha7)
- Reenable clear-cache bakery command & route caching

## [5.0.0-alpha6](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha5...5.0.0-alpha6)
- Added `AbstractInjector` Middleware.

## [5.0.0-alpha5](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha4...5.0.0-alpha5)

- [Exceptions] `SprunjeException` now extends `UserFacingException`.
- [Exceptions] `UserFacingExceptionHandler` renamed `UserMessageExceptionHandler`.
- [Exceptions] `ValidationExceptionHandler` replaced removed (`ValidationException` now handled by `UserMessageExceptionHandler`).
- [Exceptions] `UserMessageExceptionHandler` now add all `UserMessageException` interface instead of only `UserFacingException` class to alert stream and is used to handle all `UserMessageException` instead of `UserFacingException`.

## [5.0.0-alpha4](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha3...5.0.0-alpha4)

- [Exceptions] Added base `UserFacingException` and `NotFoundException`.

## [5.0.0-alpha3](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha2...5.0.0-alpha3)

- [Sprunje] Allow string for `size` and `page` option in `applyPagination`. Fix issue with Sprunje when `$request->getQueryParams()` is passed directly as Sprunje options. 
- Fix Array Cache store for testing
- Add PHP 8.2 to test suite

## [5.0.0-alpha2](https://github.com/userfrosting/sprinkle-core/compare/5.0.0-alpha1...5.0.0-alpha2)

- [Model] Fix `Call to a member function connection() on null` issue with model query builder when no ConnectionResolver was available due to Eloquent not being booted yet.
