# Schematic [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itmundi/schematic/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Build Status](https://travis-ci.org/itmundi/schematic.svg?branch=master)](https://travis-ci.org/itmundi/schematic) [![Code Coverage](https://scrutinizer-ci.com/g/itmundi/schematic/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Latest Stable Version](https://poser.pugx.org/itmundi/schematic/v/stable)](https://packagist.org/packages/itmundi/schematic) [![Total Downloads](https://poser.pugx.org/itmundi/schematic/downloads)](https://packagist.org/packages/itmundi/schematic) [![Latest Unstable Version](https://poser.pugx.org/itmundi/schematic/v/unstable)](https://packagist.org/packages/itmundi/schematic) [![License](https://poser.pugx.org/itmundi/schematic/license)](https://packagist.org/packages/itmundi/schematic)

Schematic allows you to synchronize your Craft setup over multiple environments

## Usage

Make sure you have your latest export stored at `./craft/config/schema.yml`.

Then just run to import...

```
./vendor/bin/schematic import
```

Optionally you can use --force to make the import delete any items which are not in the import file.
WARNING!! This will also delete any related content.

You can also generate a schema.yml with

```
./vendor/bin/schematic export
```

If Craft is not installed yet, Schematic will run the installer for you. Make sure you have the following environment variables set:

- CRAFT_USERNAME
- CRAFT_EMAIL
- CRAFT_PASSWORD
- CRAFT_SITENAME
- CRAFT_SITEURL
- CRAFT_LOCALE

## Overrides

You can override certain keys by placing a placeholder in `craft/config/override.yml` and setting the corresponding environment variable. The key name in the `override.yml` needs to be the same as the key you want to override from `schema.yml`, including any parent key names. The value has to start with a `%` (percentage sign) and end with one too. The correspending environment value will be `SCHEMATIC_{value_without_percentage_signs}`.

For example if you define the following `override.yml`:

```yml
parent:
    key_name: %key_value%
```

You will need to set the environment variable `SCHEMATIC_KEY_VALUE`. The value of this environment variable will override the key `key_name`. If the environment variable is not set Schematic will throw an error.


## Hooks

* Has a hook "registerMigrationService" to add exports with your own data.

```php
public function registerMigrationService()
{
    return [
		'amnav' => craft()->schematic_amNav
	];
}
```

* Has a hook to add mappings for custom field types, the `Plugin_CustomSchematicFieldModel` must extend `NerdsAndCompany\Schematic\Models\Field`

```php
public function registerSchematicFieldModels()
{
    return [
		'fieldType' => Plugin_CustomSchematicFieldModel
	];
}
```

## Changelog

###3.1.3###
 - Added array_key_exists checks for AssetField settings

###3.1.2###
 - Sections are not imported when nothing has changed
 - Fields are not imported when nothing has changed
 - Field import is repeated after everything else has been imported to make sure sources are set correctly

###3.1.1###
 - Folders are now CamelCased to add support for case-sensitive systems and PSR-4 (thanks to @ostark and @ukautz)

###3.1.0###
 - Added support for element index settings (Craft 2.5 only)

###3.0.1###
 - Schematic now also runs Craft migrations

###3.0.0###
 - Schematic is now PSR-4 compatible and uses proper autoloading
 - Renamed assets to assetSources
 - Renamed globals to globalSets

###2.0.0###
 - Reworked Schematic to install Craft when it's not installed yet
 - Added support for site locales
 - Fixed plugin installing on case-sensitive operating systems
 - Fixed field context setting too late
 - More verbose logging without backtrace

###1.4.0###
 - Reworked importing and exporting of fields
 - Added hook to allow the addition of custom logic for importing and exporting fields
 - Permissions are now sorted

###1.3.0###
 - Added the ability to use an override file

###1.2.0###
 - Use 2 spaces indent in yaml file
 - Added user fields support
 - Automatically run migrations on plugin update
 - More verbose logging in devMode

###1.1.0###
 - Replaced custom error handling with existing error handling
 - Refactored import/export with yaml support

###1.0.0###
 - Initial release

## Credits
Inspired and based on the awesome [ArtVandelay Plugin](https://github.com/xodigital/ArtVandelay)
