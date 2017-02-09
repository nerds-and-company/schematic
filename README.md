# Schematic [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itmundi/schematic/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Build Status](https://travis-ci.org/nerds-and-company/schematic.svg?branch=master)](https://travis-ci.org/nerds-and-company/schematic) [![Code Coverage](https://scrutinizer-ci.com/g/itmundi/schematic/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/itmundi/schematic/?branch=master) [![Latest Stable Version](https://poser.pugx.org/itmundi/schematic/v/stable)](https://packagist.org/packages/itmundi/schematic) [![Total Downloads](https://poser.pugx.org/itmundi/schematic/downloads)](https://packagist.org/packages/itmundi/schematic) [![Latest Unstable Version](https://poser.pugx.org/itmundi/schematic/v/unstable)](https://packagist.org/packages/itmundi/schematic) [![License](https://poser.pugx.org/itmundi/schematic/license)](https://packagist.org/packages/itmundi/schematic)

Schematic allows you to synchronize your Craft setup over multiple environments. It does this by exporting information about assets,  database (fields, sections, users), locales and plugins to a YAML file that can be imported in other environments.

## Installation

This tool can be installed manually or [using Composer](https://getcomposer.org/doc/00-intro.md).

### Composer

The preferred means of installation is through Composer. Run the following command from the root of your project:

```
composer require itmundi/schematic
```

This will add `itmundi/schematic` as a requirement to your  project's `composer.json` file and install the source-code into the `vendor/itmundi/schematic` directory. Composer will also create the executable `vendor/bin/schematic`.

### Manual

If installation through Composer is not an option, the package can also be installed manually. Download [the latest release](https://github.com/itmundi/schematic/releases/latest) or clone the contents of this repository into your project.
The executable is located in at `bin/schematic`.

## Usage

### Basic usage

The most common usage pattern of this tool, to synchronize from a development to a production environment, would be:

1. Create a Craft project locally
2. Set up all of the desired plugins, sections, templates, etc.
3. Run a Schematic export locally
4. Optionally, if a revision control system is used, commit the schema file to the local repository
5. Deploy the Craft application to a prodcution environment
6. Run a Schematic import remotely

Or, to synchronize from a production to a development environment:

1. Run a Schematic export remotely
2. Pull the schema file locally
3. Optionally, if a revision control system is used, commit the schema file to the local repository
4. Run a Schematic import locally

### Exporting

An export can be created by running:

```
./vendor/bin/schematic export
```

To skip exporting a specific of data type, exclusions can be specified in the following form:

```
./vendor/bin/schematic export --exclude=assetSources
```

Multiple exclusions can also be specified:

```
./vendor/bin/schematic export --exclude=assetSources --exclude=categoryGroups
```

Here is a list of all of the data types and their corresponding exclude parameter values:

| Data Type | Exlude Parameter |
| ------------- |-------------|
| Asset Sources | assetSources |
| Category Groups | categoryGroups |
| Element Indexes | elementIndexSettings |
| Fields | fields |
| Global Sets | globalSets |
| Locales | locales |
| Plugins | plugins |
| Plugin Data | pluginData |
| Sections | sections |
| Tag Groups | tagGroups |
| Users | users |
| User Groups | userGroups |

When craft is in a different directory than `./craft/app`, set the path with an environment variable.
For example:
```
export CRAFT_APP_PATH=./vendor/craft/app; ./bin/schematic export
```

This will generate a schema file at `craft/config/schema.yml`. The file path can be changed using the `--file` flag, for instance `schematic export --file=path/to/my-schema.yml`

If Craft is not installed yet, Schematic will run the installer for you. Make sure the following environment variables have been set:

- CRAFT_USERNAME
- CRAFT_EMAIL
- CRAFT_PASSWORD
- CRAFT_SITENAME
- CRAFT_SITEURL
- CRAFT_LOCALE

Optional environment variables (similar to the [PHP constants](https://craftcms.com/docs/php-constants)):
- CRAFT_BASE_PATH
- CRAFT_APP_PATH
- CRAFT_VENDOR_PATH
- CRAFT_FRAMEWORK_PATH
- CRAFT_CONFIG_PATH
- CRAFT_PLUGINS_PATH
- CRAFT_STORAGE_PATH
- CRAFT_TEMPLATES_PATH
- CRAFT_TRANSLATIONS_PATH
- CRAFT_ENVIRONMENT


### Importing

To run an import with schematic, a schema file needs to be present. An import can be created by running:

```
./vendor/bin/schematic import
```

By default schematic will look at `./craft/config/schema.yml`. To change the path where schematic will look for the schema file, use the `--file` flag.

Optionally the `--force` flag can be used to make the import delete any items which are not mentioned in the import file.

**WARNING!!** This will also delete any _related_ content.

Keys in the schema file can be overridden by passing an override file to schematic using the `--override_file` flag, for instance: `vendor/bin/schematic import --override_file=craft/config/override.yml`.

### Overrides

Specific keys can be overriden by adding a key in `craft/config/override.yml` and setting the corresponding environment variable. The key name in the `override.yml` needs to be the same as the key you want to override from `schema.yml`, including any parent key names. The value has to start and end with a `%` (percentage sign). The correspending environment value will be `SCHEMATIC_{value_without_percentage_signs}`.

#### Example

If the following `override.yml` is defined:

```yml
parent:
    key_name: %key_value%
```

Then the environment variable `SCHEMATIC_KEY_VALUE` needs to be set. The value of this environment variable will override the key `key_name`. If the environment variable is not set Schematic will throw an error.

### Hooks

This tool has two hooks that extending code can plug in to. An example of a project using these hooks is the [Schematic plugin for AmNav](https://github.com/nerds-and-company/schematic-amnav).


#### registerMigrationService

<table>
<tr><td>Called by</td><td><code>NerdsAndCompany\Schematic\Services\Schematic::importFromYaml()</code>,  <code>NerdsAndCompany\Schematic\Services\Schematic::exportToYaml()</code></td></tr>
<tr><td>Return</td><td>An array where the keys are a name and the values are a Migration Service.</td></tr>
</table>

Gives plugins a chance to register their own Migration Services to Schematic in order to import or exports their own data.

```php
public function registerMigrationService()
{
    return [
		'amnav' => craft()->schematic_amNav
	];
}
```

#### registerSchematicFieldModels

<table>
<tr><td>Called by</td><td><code>NerdsAndCompany\Schematic\Models\FieldFactory::build()</code></td></tr>
<tr><td>Return</td><td>An array where the keys are a the name of the fieldtype the mapping is for and the values are the <a href="http://php.net/manual/en/language.namespaces.rules.php">fully qualified name</a> of the custom field model class.
</td></tr>
</table>

Gives plugins a chance to add custom mappings to Schematic for custom field types.

```php
public function registerSchematicFieldModels()
{
    return [
		'fieldType' => Plugin_CustomSchematicFieldModel
	];
}
```

A plugin that want to make use of this functionality needs to extend `NerdsAndCompany\Schematic\Models\Field`, for example

```php
<?php

namespace Craft;

class Plugin_CustomSchematicFieldModel extends \NerdsAndCompany\Schematic\Models\Field
{
    //…
}

```

## License

This project has been licensed under the MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

###3.5.2###
- Updated Console component with latest Craft updates and fixes
- Updated YAML component

###3.5.1###
- Log error in stead of throwing exception when failing to save new plugin info
- Added support for >= Craft 2.6.2951's new constants, CRAFT_VENDOR_PATH and CRAFT_FRAMEWORK_PATH

###3.5.0###
- Added ability to exclude datatypes from export. (thanks to @spoik)

###3.4.3###
- Set publicURLs to true by default for asset sources to be compatible with craft 2.6.2794

###3.4.2###
- Fixed asset import bug where the source was never set. (thanks to @roelvanhintum)

###3.4.1###
- Fix elevated user sessions (closes #59)
- Bugfix in field asset sources

###3.4.0###
- Schematic now also exports and imports tag groups

###3.3.2###
- Allow 'singles' as a source

###3.3.1###
- Return empty string when field source not found at import

###3.3.0###
- Schematic now also exports and imports category groups (thanks to @smcyr, closes #31)
- Only run updateDatabase for craft when craft db migrations are needed

###3.2.2###
- Improved the Craft and plugin updating/migrating mechanism
- Fixed a bug where element index settings wheren't imported (closes #49)

###3.2.1###
- Also delete entrytypes which are not in the schema when using force

###3.2.0###
- Added ability to set craft constants through env variables (thanks to @roelvanhintum)
- Fixed assetsource fieldlayout backwards compatibility

###3.1.6###
- Adds install and more detailed usage documentation

###3.1.5###
 - Added support for Asset fieldlayouts (thanks to @roelvanhintum)

###3.1.4###
 - Reset craft field service cache before each import
 - Get section entry types by section id in stead of from section

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
Inspired and based on the awesome [ArtVandelay Plugin](https://github.com/xodigital/ArtVandelay) and build by [these awesome individuals](https://github.com/itmundi/schematic/graphs/contributors)
