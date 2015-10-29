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

You can override certain keys by placing them in the file `craft/config/override.yml`. When you place this file in gitignore, you can have different settings for different environments. All the base settings go in `schema.yml` and any specific overrides in `override.yml`.

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

## Changelog

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
