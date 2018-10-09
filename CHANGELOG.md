# Twigpack Changelog

## 1.0.6 - 2018-10-08
### Added
* Strings passed in to `manifestPath` can now be Yii2 aliases as well
* Added `craft.twigpack.includeInlineCssTags()`
* Added `craft.twigpack.includeCriticalCssTags()`
* Added `craft.twigpack.includeFile()`

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
