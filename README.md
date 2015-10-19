# Schematic

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

###1.0.0###
 - Initial release

## Credits
Inspired and based on the awesome [ArtVandelay Plugin](https://github.com/xodigital/ArtVandelay)
