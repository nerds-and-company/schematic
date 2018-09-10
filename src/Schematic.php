<?php

namespace NerdsAndCompany\Schematic;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use yii\helpers\Console;
use NerdsAndCompany\Schematic\DataTypes\AssetTransformDataType;
use NerdsAndCompany\Schematic\DataTypes\CategoryGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\ElementIndexDataType;
use NerdsAndCompany\Schematic\DataTypes\EmailSettingsDataType;
use NerdsAndCompany\Schematic\DataTypes\FieldDataType;
use NerdsAndCompany\Schematic\DataTypes\GeneralSettingsDataType;
use NerdsAndCompany\Schematic\DataTypes\GlobalSetDataType;
use NerdsAndCompany\Schematic\DataTypes\PluginDataType;
use NerdsAndCompany\Schematic\DataTypes\SectionDataType;
use NerdsAndCompany\Schematic\DataTypes\SiteDataType;
use NerdsAndCompany\Schematic\DataTypes\TagGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\UserGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\UserSettingsDataType;
use NerdsAndCompany\Schematic\DataTypes\VolumeDataType;
use NerdsAndCompany\Schematic\Mappers\ElementIndexMapper;
use NerdsAndCompany\Schematic\Mappers\EmailSettingsMapper;
use NerdsAndCompany\Schematic\Mappers\GeneralSettingsMapper;
use NerdsAndCompany\Schematic\Mappers\ModelMapper;
use NerdsAndCompany\Schematic\Mappers\PluginMapper;
use NerdsAndCompany\Schematic\Mappers\UserSettingsMapper;
use NerdsAndCompany\Schematic\Interfaces\ConverterInterface;
use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use NerdsAndCompany\Schematic\Events\ConverterEvent;

/**
 * Schematic.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Schematic extends Plugin
{
    const EVENT_RESOLVE_CONVERTER = 'resolve_converter';
    const EVENT_MAP_SOURCE = 'map_source';

    /**
     * @var string
     */
    public $controllerNamespace = 'NerdsAndCompany\Schematic\Controllers';

    /**
     * @var array
     */
    public $dataTypes = [];

    /**
     * Initialize the module.
     */
    public function init()
    {
        Craft::setAlias('@NerdsAndCompany/Schematic', __DIR__);

        $config = [
            'components' => [
                'elementIndexMapper' => [
                    'class' => ElementIndexMapper::class,
                ],
                'emailSettingsMapper' => [
                    'class' => EmailSettingsMapper::class,
                ],
                'generalSettingsMapper' => [
                    'class' => GeneralSettingsMapper::class,
                ],
                'modelMapper' => [
                    'class' => ModelMapper::class,
                ],
                'pluginMapper' => [
                    'class' => PluginMapper::class,
                ],
                'userSettingsMapper' => [
                    'class' => UserSettingsMapper::class,
                ],
            ],
            'dataTypes' => [
                'plugins' => PluginDataType::class,
                'sites' => SiteDataType::class,
                'volumes' => VolumeDataType::class,
                'assetTransforms' => AssetTransformDataType::class,
                'emailSettings' => EmailSettingsDataType::class,
                'fields' => FieldDataType::class,
                'generalSettings' => GeneralSettingsDataType::class,
                'sections' => SectionDataType::class,
                'globalSets' => GlobalSetDataType::class,
                'categoryGroups' => CategoryGroupDataType::class,
                'tagGroups' => TagGroupDataType::class,
                'userGroups' => UserGroupDataType::class,
                'userSettings' => UserSettingsDataType::class,
                'elementIndexSettings' => ElementIndexDataType::class,
            ],
        ];

        Craft::configure($this, $config);

        parent::init();
    }

    /**
     * Get datatype by handle.
     *
     * @param string $dataTypeHandle
     *
     * @return DateTypeInterface|null
     */
    public function getDataType(string $dataTypeHandle)
    {
        if (!isset($this->dataTypes[$dataTypeHandle])) {
            Schematic::error('DataType '.$dataTypeHandle.' is not registered');

            return null;
        }

        $dataTypeClass = $this->dataTypes[$dataTypeHandle];
        if (!class_exists($dataTypeClass)) {
            Schematic::error('Class '.$dataTypeClass.' does not exist');

            return null;
        }

        $dataType = new $dataTypeClass();
        if (!$dataType instanceof DataTypeInterface) {
            Schematic::error($dataTypeClass.' does not implement DataTypeInterface');

            return null;
        }

        return $dataType;
    }

    /**
     * Check mapper handle is valid.
     *
     * @param string $mapper
     *
     * @return bool
     */
    public function checkMapper(string $mapper): bool
    {
        if (!isset($this->$mapper)) {
            Schematic::error('Mapper '.$mapper.' not found');

            return false;
        }
        if (!$this->$mapper instanceof MapperInterface) {
            Schematic::error(get_class($this->$mapper).' does not implement MapperInterface');

            return false;
        }

        return true;
    }

    /**
     * Find converter for model class.
     *
     * @param string $modelClass
     *
     * @return ConverterInterface|null
     */
    public function getConverter(string $modelClass, string $originalClass = '')
    {
        if ('' === $originalClass) {
            $originalClass = $modelClass;
        }

        $converterClass = 'NerdsAndCompany\\Schematic\\Converters\\'.ucfirst(str_replace('craft\\', '', $modelClass));
        $event = new ConverterEvent([
            'modelClass' => $modelClass,
            'converterClass' => $converterClass,
        ]);

        $this->trigger(self::EVENT_RESOLVE_CONVERTER, $event);
        $converterClass = $event->converterClass;

        if (class_exists($converterClass)) {
            $converter = new $converterClass();
            if ($converter instanceof ConverterInterface) {
                return $converter;
            }
        }

        $parentClass = get_parent_class($modelClass);
        if (!$parentClass) {
            Schematic::error('No converter found for '.$originalClass);

            return null;
        }

        return $this->getConverter($parentClass, $originalClass);
    }

    /**
     * Is force enabled?
     *
     * @var bool
     */
    public static $force = false;

    /**
     * Logs an error message.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     *                              complex data structure, such as array.
     */
    public static function error($message)
    {
        Craft::$app->controller->stdout($message.PHP_EOL, Console::FG_RED);
    }

    /**
     * Logs a warning message.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     *                              complex data structure, such as array.
     */
    public static function warning($message)
    {
        Craft::$app->controller->stdout($message.PHP_EOL, Console::FG_YELLOW);
    }

    /**
     * Logs an info message.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     *                              complex data structure, such as array.
     */
    public static function info($message)
    {
        Craft::$app->controller->stdout($message.PHP_EOL);
    }

    /**
     * Log an import error.
     *
     * @param Model  $record
     * @param string $handle
     */
    public static function importError(Model $record, string $handle)
    {
        static::warning('- Error importing '.get_class($record).' '.$handle);
        $errors = $record->getErrors();
        if (!is_array($errors)) {
            static::error('   - An unknown error has occurred');

            return;
        }
        foreach ($errors as $subErrors) {
            foreach ($subErrors as $error) {
                static::error('   - '.$error);
            }
        }
    }
}
