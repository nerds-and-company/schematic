# Schematic [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itmundi/schematic/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Build Status](https://travis-ci.org/itmundi/schematic.svg?branch=master)](https://travis-ci.org/itmundi/schematic) [![Code Coverage](https://scrutinizer-ci.com/g/itmundi/schematic/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Latest Stable Version](https://poser.pugx.org/itmundi/schematic/v/stable)](https://packagist.org/packages/itmundi/schematic) [![Total Downloads](https://poser.pugx.org/itmundi/schematic/downloads)](https://packagist.org/packages/itmundi/schematic) [![Latest Unstable Version](https://poser.pugx.org/itmundi/schematic/v/unstable)](https://packagist.org/packages/itmundi/schematic) [![License](https://poser.pugx.org/itmundi/schematic/license)](https://packagist.org/packages/itmundi/schematic)

Schematic allows you to synchronize your Craft setup over multiple environments

## Usage

Make sure you have your latest export stored at `./craft/config/schema.yml`.

Then just run to import...

```
./craft/app/etc/console/yiic schematic import
```

Optionally you can use --force to make the import delete any items which are not in the import file.
WARNING!! This will also delete any related content.

You can also generate a schema.yml with

```
./craft/app/etc/console/yiic schematic export
```

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
    return array(
		'amnav' => craft()->schematic_amNav
	);
}
```

* Has a hook to add mappings for custom field types, the Plugin_CustomSchematicFieldModel must extend the Schematic_FieldModel

```php
public function registerSchematicFieldModels()
{
    return array(
		'fieldType' => Plugin_CustomSchematicFieldModel
	);
}
```

## Changelog

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
