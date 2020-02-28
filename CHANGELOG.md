# Twigpack Changelog

## 1.2.0 - 2020-02-28
### Changed
* Switched over to using `media="print"` for asynchronously loading a stylesheet as per [The Simplest Way to Load CSS Asynchronously](https://www.filamentgroup.com/lab/load-css-simpler/)
* Deprecated `craft.twigpack.includeCssRelPreloadPolyfill()` since it is no longer necessary. It now does nothing

## 1.1.14 - 2020-02-04
### Added
* If this is a dev-server, look for the modern manifest file first

## 1.1.13 - 2020-01-22
### Added
* Added the config setting `useAbsoluteUrl` to control whether module URLs will be forced to be fully qualified aboslute URLs

### Changed
* If we're in `devMode` invalidate the cache immediately
* If an error is reported, and `self::$isHot` is `true` log it as a warning, not an error

## 1.1.12 - 2019-11-12
### Fixed
* Fixed an issue with `includeFileFromManifest()` not working due to recent changes

## 1.1.11 - 2019-10-15
### Changed
* Revert a PR that caused Twigpack to no longer gracefully fall back on locally built assets if the `webpack-dev-server` is not running
* Add a short timeout to `file_get_contents` to prevent it from taking too long if the file doesn't exist

## 1.1.10 - 2019-10-03
### Changed
* Changed examples to use `’dev’` for local development (instead of `’local’`)

## 1.1.9 - 2019-10-01
### Added
* Added `getModuleHash()` function, to grab the hash key of a module

### Changed
* Fixed an issue where `isHot` was not set before use
* Fixed `getFileFromManifest()` to load files from webpack-dev-server

## 1.1.8 - 2019-08-06
### Changed
* Added `cacheKeySuffix` to the Settings model

## 1.1.7 - 2019-06-05
### Changed
* Clarify expected output with no second param
* Code cleanup

## 1.1.6 - 2019-05-05
### Changed
* Fixed an issue where `null` could potentially be passed in to `resolveTemplate()`

## 1.1.5 - 2019-03-24
### Changed
* Fixed a typo in the `twigpack-manifest-cache` cache key
* Changed deprecated `\Twig_Markup` to `\Twig\Markup`
* Elaborated on Twigpack's caching and how to clear it in the `README.md`

## 1.1.4 - 2019-01-22
### Changed
* Handle the case where there is an error decoding the JSON from the manifest
* Updated the documentation to reflect using `@webroot/` by default for the `server` `manifestPath`

## 1.1.3 - 2018-10-31
### Changed
* Make `includeCriticalCssTags()` and `includeInlineCssTags()` soft errors that do nothing if the file is missing

## 1.1.2 - 2018-10-25
### Added
* Added the ability for Hot Module Replacement (HMR) to work through Twig error template pages via the `errorEntry` setting in `config.php`

## 1.1.1 - 2018-10-16
### Changed
* Fixed an issue where if the `manifest.json` was served remotely via https, Twigpack was unable to load it
* Made all errors "soft" for missing CSS/JS modules, so a warning will be logged, but life continues on

## 1.1.0 - 2018-10-09
### Added
* Strings passed in to `manifestPath` can now be Yii2 aliases as well
* Added `craft.twigpack.includeFile()`
* Added `craft.twigpack.includeFileFromManifest()`
* Added `craft.twigpack.includeInlineCssTags()`
* Added `craft.twigpack.includeCriticalCssTags()`

## 1.0.5 - 2018-09-28
### Changed
* Check via `empty()` rather than `!== null` when checking the manifest for module entries
* CSS module loading generates a soft error now, rather than throwing an `NotFoundHttpException`

## 1.0.4 - 2018-09-28
### Added
* Added `this.onload=null;` to async CSS link tag
* Added `craft.twigpack.includeCssRelPreloadPolyfill()`

### Changed
* Better error reporting if modules don't exist in the manifest

## 1.0.3 - 2018-09-24
### Changed
* Allow the `manifestPath` to be a file system path or a URI

## 1.0.2 - 2018-09-23
### Added
* Added `getModuleUri()` function
* Added `getManifestFile()` function

### Changed
* Fixed return types to allow for null
* Code refactoring

## 1.0.1 - 2018-09-22
### Added
* Better error logging if the manifest file can't be found (check `storage/logs/web.log`)
* Throw a `NotFoundHttpException` if the `manifest.json` cannot be found

## 1.0.0 - 2018-09-21
### Added
* Initial release
