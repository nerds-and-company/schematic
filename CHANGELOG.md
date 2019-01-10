## 4.1.1 - 2019-01-08
### Fixed
- Fixed "assign user group" permissions not being exported
- Fixed issue where global set sources weren't linked because they were cached

## 4.1.0 - 2018-11-19
### Added
- Added more flexibility for getting a record's index

## 4.0.18 - 2018-11-19
### Added
- Delete empty field and site groups on import with force

### Fixed
- Fixed user field sources not being exported
- Fixed category field layout not properly importing for existing categories
- Fixed import of multiple sites in same sitegroup

## 4.0.17 - 2018-09-24
### Fixed
- Fixed backwards compatibility of element index mapper
- Fixed issue with volume and volume folder ids going out of sync

## 4.0.16 - 2018-09-11
### Added
- Added map_source event for mapping custom sources. See the Events section in the README for more.

## 4.0.15 - 2018-08-31
### Added
- Schematic can now parse environment variables in the schema file directly, without need for an override file
- Used environment variables don't have to be prefixed with SCHEMATIC_ anymore
- Environment variables without SCHEMATIC_ prefix are now case-sensitive

## 4.0.14 - 2018-07-26
### Fixed
- Fixed a bug where element indexes with custom elements failed to import

## 4.0.13 - 2018-07-18
### Added
- Added ability to use a custom key to index by for model converters

## 4.0.12 - 2018-07-11
### Added
- Added afterImport callback for datatypes

### Fixed
- Fixed issue where global sets weren't properly imported in some cases

## 4.0.11 - 2018-06-21
### Added
- Added General Settings support

## 4.0.10 - 2018-06-07
### Fixed
- Fixed issue handling "edit site" permissions

## 4.0.9 - 2018-06-06
### Added
- Schematic now recursively parses source id's to handles and vice versa

## 4.0.8 - 2018-06-01
### Added
- Schematic now also uses the override file when exporting
- Added support for "singles" sources

### Fixed
- Fixed issue where category site settings weren't properly imported in some cases

## 4.0.7 - 2018-05-22
### Fixed
- Fixed issues with importing element index settings

## 4.0.6 - 2018-05-13
### Fixed
- Fixed override file command option to be in kebab case

## 4.0.5 - 2018-05-03
### Added
- Added ability to inject custom converters

### Fixed
- Fixed a bug where matrix fields got imported into the wrong content table
- Fixed a bug where matrix fields got imported with the wrong field context
- Fixed a bug where sources didn't get mapped correctly in some cases

## 4.0.4 - 2018-04-28
### Fixed
- Fixed a bug where categories field source was not imported

## 4.0.3 - 2018-04-24
### Fixed
- Fixed a bug where fields weren't properly skipped when importing a second time

## 4.0.2 - 2018-04-20
### Added
- Added Email Settings support

## 4.0.1 - 2018-04-18
### Added
- Added PHP 7.0 compatibility
- Added new plugin icon
- Added more plugin metadata for correct plugin store display

## 4.0.0 - 2018-04-16
### Added
- Initial release for Craft 3
