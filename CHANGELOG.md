### 3.8.12 ###
- Do last fields import after hook imports

### 3.8.11 ###
- Fixed registerSchematicSources hook

### 3.8.10 ###
- Added dummy getTheme method to schematic application

### 3.8.9 ###
- Fixed assignUserGroup permission serialization bug

### 3.8.8 ###
- Added ability to exclude datatypes on import (thanks @Zae)

### 3.8.7 ###
- Fixes missing field errors

### 3.8.6 ###
- Fixed a bug where element index settings source order was not synced

### 3.8.5 ###
- Make sure field context is set to global for global fields

### 3.8.4 ###
- Added hook to map custom sources

### 3.8.3 ###
- Fixed a bug where editLocale permission could not be synced
- Use CRAFT_FRAMEWORK_PATH to locate framework files (thanks to @dnunez24)

### 3.8.2 ###
- Fixed a bug where schematic would crash when a source does not exist yet
- Fixed a bug where permission category ids were converted to section handles
- Added tests for the sources service

### 3.8.1 ###
- Check if element index sources exist

### 3.8.0 ###
- Renamed package to nerds-and-company/schematic
- Added info about nerds-and-company/schematic-commerce to README
- Small fixes

### 3.7.2 ###
- Use handles for element index settings

### 3.7.1 ###
- Removed Asset Transform dimensionChangeTime attribute from schema

### 3.7.0 ###
- Added support for importing/exporting Asset Transforms
- Asset Sources now respect the force option

### 3.6.1 ###
- Don't care about instable plugin database update outcome

### 3.6.0 ###
- Delete old blocktypes from matrixfields on force. (thanks to @Zae)
- Beware backwards compatibility for the custom Field Models. There is now a $force parameter on the populate method.

### 3.5.2 ###
- Fixed a bug where removed fields were not flushed properly from the in-memory cache
- Updated Console component with latest Craft updates and fixes
- Updated YAML component

### 3.5.1 ###
- Log error in stead of throwing exception when failing to save new plugin info
- Added support for >= Craft 2.6.2951's new constants, CRAFT_VENDOR_PATH and CRAFT_FRAMEWORK_PATH

### 3.5.0 ###
- Added ability to exclude datatypes from export. (thanks to @spoik)

### 3.4.3 ###
- Set publicURLs to true by default for asset sources to be compatible with craft 2.6.2794

### 3.4.2 ###
- Fixed asset import bug where the source was never set. (thanks to @roelvanhintum)

### 3.4.1 ###
- Fix elevated user sessions (closes #59)
- Bugfix in field asset sources

### 3.4.0 ###
- Schematic now also exports and imports tag groups

### 3.3.2 ###
- Allow 'singles' as a source

### 3.3.1 ###
- Return empty string when field source not found at import

### 3.3.0 ###
- Schematic now also exports and imports category groups (thanks to @smcyr, closes #31)
- Only run updateDatabase for craft when craft db migrations are needed

### 3.2.2 ###
- Improved the Craft and plugin updating/migrating mechanism
- Fixed a bug where element index settings wheren't imported (closes #49)

### 3.2.1 ###
- Also delete entrytypes which are not in the schema when using force

### 3.2.0 ###
- Added ability to set craft constants through env variables (thanks to @roelvanhintum)
- Fixed assetsource fieldlayout backwards compatibility

### 3.1.6 ###
- Adds install and more detailed usage documentation

### 3.1.5 ###
 - Added support for Asset fieldlayouts (thanks to @roelvanhintum)

### 3.1.4 ###
 - Reset craft field service cache before each import
 - Get section entry types by section id in stead of from section

### 3.1.3 ###
 - Added array_key_exists checks for AssetField settings

### 3.1.2 ###
 - Sections are not imported when nothing has changed
 - Fields are not imported when nothing has changed
 - Field import is repeated after everything else has been imported to make sure sources are set correctly

### 3.1.1 ###
 - Folders are now CamelCased to add support for case-sensitive systems and PSR-4 (thanks to @ostark and @ukautz)

### 3.1.0 ###
 - Added support for element index settings (Craft 2.5 only)

### 3.0.1 ###
 - Schematic now also runs Craft migrations

### 3.0.0 ###
 - Schematic is now PSR-4 compatible and uses proper autoloading
 - Renamed assets to assetSources
 - Renamed globals to globalSets

### 2.0.0 ###
 - Reworked Schematic to install Craft when it's not installed yet
 - Added support for site locales
 - Fixed plugin installing on case-sensitive operating systems
 - Fixed field context setting too late
 - More verbose logging without backtrace

### 1.4.0 ###
 - Reworked importing and exporting of fields
 - Added hook to allow the addition of custom logic for importing and exporting fields
 - Permissions are now sorted

### 1.3.0 ###
 - Added the ability to use an override file

### 1.2.0 ###
 - Use 2 spaces indent in yaml file
 - Added user fields support
 - Automatically run migrations on plugin update
 - More verbose logging in devMode

### 1.1.0 ###
 - Replaced custom error handling with existing error handling
 - Refactored import/export with yaml support

### 1.0.0 ###
 - Initial release
