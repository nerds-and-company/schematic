# Schematic (for Craft 3) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nerds-and-company/schematic/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/schematic/?branch=master) [![Build Status](https://travis-ci.org/nerds-and-company/schematic.svg?branch=master)](https://travis-ci.org/nerds-and-company/schematic) [![Code Coverage](https://scrutinizer-ci.com/g/nerds-and-company/schematic/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/schematic/?branch=master) [![Latest Stable Version](https://poser.pugx.org/nerds-and-company/schematic/v/stable)](https://packagist.org/packages/nerds-and-company/schematic) [![Total Downloads](https://poser.pugx.org/nerds-and-company/schematic/downloads)](https://packagist.org/packages/nerds-and-company/schematic) [![Latest Unstable Version](https://poser.pugx.org/nerds-and-company/schematic/v/unstable)](https://packagist.org/packages/nerds-and-company/schematic) [![License](https://poser.pugx.org/nerds-and-company/schematic/license)](https://packagist.org/packages/nerds-and-company/schematic)

Schematic allows you to synchronize your Craft setup over multiple environments. It does this by exporting information about assets,  database (fields, sections, users), locales and plugins to a YAML file that can be imported in other environments.

## :exclamation: Craft 3.1 notice

Craft 3.1 brings [project config](https://github.com/craftcms/cms/blob/3.1/docs/project-config.md) that will deliver most if not all schematic functionality natively.
We will continue to support schematic going forward, but it seems likely to become obsolete.

## Installation

This tool can be installed [using Composer](https://getcomposer.org/doc/00-intro.md). Run the following command from the root of your project:

```
composer require nerds-and-company/schematic
```

This will add `nerds-and-company/schematic` as a requirement to your  project's `composer.json` file and install the source-code into the `vendor/nerds-and-company/schematic` directory.

Schematic is now available as an installable plugin in Craft. You can install it in the cp or use `./craft install/plugin schematic`

## Usage

### Basic usage

The most common usage pattern of this tool, to synchronize from a development to a production environment, would be:

1. Create a Craft project locally
2. Set up all of the desired plugins, sections, templates, etc.
3. Run a Schematic export locally
4. Optionally, if a revision control system is used, commit the schema file to the local repository
5. Deploy the Craft application to a production environment
6. Run a Schematic import remotely

Or, to synchronize from a production to a development environment:

1. Run a Schematic export remotely
2. Pull the schema file locally
3. Optionally, if a revision control system is used, commit the schema file to the local repository
4. Run a Schematic import locally

### Exporting

An export can be created by running:

```
./craft schematic/export
```

To skip exporting a specific of data type, exclusions can be specified in the following form:

```
./craft schematic/export --exclude=volumes
```

Multiple exclusions can also be specified:

```
./craft schematic/export --exclude=volumes,categoryGroups
```

The same goes for only exporting a subset of data types:

```
./craft schematic/export --include=volumes,categoryGroups
```

See [Supported DataTypes](#Supported DataTypes)

An export will generate a schema file at `config/schema.yml`. The file path can be changed using the `--file` flag, for instance `craft schematic/export --file=path/to/my-schema.yml`

### Importing

To run an import with schematic, a schema file needs to be present. An import can be created by running:

```
./craft schematic/import
```

By default schematic will look at `config/schema.yml`. To change the path where schematic will look for the schema file, use the `--file` flag.

Optionally the `--force` flag can be used to make the import delete any items which are not mentioned in the import file.

**WARNING!!** This will also delete any _related_ content.

To skip importing a specific of data type, exclusions can be specified in the following form:

```
./craft schematic/import --exclude=volumes
```

Multiple exclusions can also be specified:

```
./craft schematic/import --exclude=volumes,categoryGroups
```

See [Supported DataTypes](#Supported DataTypes)

### Supported DataTypes

Here is a list of all of the data types and their corresponding exclude parameter values:

| Data Type | Exclude/Include Parameter |
| ------------- |-------------|
| plugins | plugins |
| Sites | sites |
| Asset Transforms | assetTransforms |
| Category Groups | categoryGroups |
| Element Indexes | elementIndexSettings |
| Email Settings | emailSettings |
| Fields | fields |
| General Settings | generalSettings |
| Global Sets | globalSets |
| Plugins | plugins |
| Sections | sections |
| Tag Groups | tagGroups |
| User Settings | userSettings |
| User Groups | userGroups |
| Volumes | volumes |

### Overrides and environment variables

Specific keys can be overriden by adding a key in `config/override.yml` and setting the corresponding environment variable. The key name in the `override.yml` needs to be the same as the key you want to override from `schema.yml`, including any parent key names.

The override file is also applied back when exporting, so your variables are not overriden by actual values. Schematic also supports passing an override file using the `--override-file` flag, for instance: `./craft schematic/import --override-file=path/to/your/config/override.yml`.

#### Example

If the following `override.yml` is defined:

```yml
parent:
    key_name: %KEY_VALUE%
```

Then the environment variable `KEY_VALUE` needs to be set. The value of this environment variable will override the key `key_name`. If the environment variable is not set Schematic will throw an error.

Environment variables can also directly be used in the `schema.yml` file. Beware that if you do that, they will be overriden on export by their environment variable's values.

### Events

Custom converters can be injected with the `EVENT_RESOLVE_CONVERTER` event.
This can be especially useful for importing and exporting custom field types.
The converters need to implement the `NerdsAndCompany\Schematic\Interfaces\ConverterInterface`.

```php
Event::on(Schematic::class, Schematic::EVENT_RESOLVE_CONVERTER, function (ConverterEvent $event) {
    if ($event->modelClass = "My\Custom\Field") {
      $event->converterClass = "My\Custom\FieldConverter";
    }
});
```

Custom source mappings can be injected with the `EVENT_MAP_SOURCE` event.
This can be especially useful for importing and exporting custom sources.

```php
Event::on(Schematic::class, Schematic::EVENT_MAP_SOURCE, function (SourceMappingEvent $event) {
    list($sourceType, $sourceFrom) = explode(':', $event->source);

    switch ($sourceType) {
        case 'plugin-source':
            $event->service = Craft::$app->customService;
            $event->method = 'getCustomModelBy';
            break;
    }
});
```

### Caveats

Schematic uses handles to identify content. When a handle is changed in the schema file and import is run with force a new content type will be created with that handle, and the old content type will be deleted!

It is recommended to change content type handles with craft content migrations and to run these migration before running a schematic import.

## License

This project has been licensed under the MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

[CHANGELOG.md](CHANGELOG.md)

## Credits
Inspired and based on the awesome [ArtVandelay Plugin](https://github.com/xodigital/ArtVandelay) and built by [these awesome individuals](https://github.com/nerds-and-company/schematic/graphs/contributors)
