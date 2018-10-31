# Twigpack Changelog

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
