<?php

namespace NerdsAndCompany\Schematic;

use Craft;
use craft\base\Model;
use yii\base\Module;
use yii\helpers\Console;
use NerdsAndCompany\Schematic\DataTypes\AssetTransformDataType;
use NerdsAndCompany\Schematic\DataTypes\CategoryGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\ElementIndexDataType;
use NerdsAndCompany\Schematic\DataTypes\FieldDataType;
use NerdsAndCompany\Schematic\DataTypes\GlobalSetDataType;
use NerdsAndCompany\Schematic\DataTypes\PluginDataType;
use NerdsAndCompany\Schematic\DataTypes\SectionDataType;
use NerdsAndCompany\Schematic\DataTypes\SiteDataType;
use NerdsAndCompany\Schematic\DataTypes\TagGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\UserGroupDataType;
use NerdsAndCompany\Schematic\DataTypes\UserSettingsDataType;
use NerdsAndCompany\Schematic\DataTypes\VolumeDataType;
use NerdsAndCompany\Schematic\Mappers\ElementIndexMapper;
use NerdsAndCompany\Schematic\Mappers\ModelMapper;
use NerdsAndCompany\Schematic\Mappers\PluginMapper;
use NerdsAndCompany\Schematic\Mappers\UserSettingsMapper;

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
class Schematic extends Module
{
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
                'fields' => FieldDataType::class,
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
        foreach ($record->getErrors() as $errors) {
            foreach ($errors as $error) {
                static::error('   - '.$error);
            }
        }
    }
}
