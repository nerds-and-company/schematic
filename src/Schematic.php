<?php

namespace NerdsAndCompany\Schematic;

use Craft;
use craft\base\Model;

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
 */
class Schematic
{
    /**
     * The available datatypes.
     *
     * @TODO: Make data types and processor components configurable.
     *
     * @var array
     */
    const DATA_TYPES = [
        'sites' => Services\ModelProcessor::class,
        'volumes' => Services\ModelProcessor::class,
        'assetTransforms' => Services\ModelProcessor::class,
        'fields' => Services\ModelProcessor::class,
        'plugins' => Services\Plugins::class,
        'sections' => Services\ModelProcessor::class,
        'globalSets' => Services\ModelProcessor::class,
        'userGroups' => Services\ModelProcessor::class,
        'users' => Services\Users::class,
        'categoryGroups' => Services\ModelProcessor::class,
        'tagGroups' => Services\ModelProcessor::class,
        'elementIndexSettings' => Services\ElementIndexSettings::class,
    ];

    /**
     * Get records for datatype.
     *
     * @TODO: Make this more dynamic
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param string $datatype
     *
     * @return Model[]
     */
    public static function getRecords(string $datatype)
    {
        $records = [];
        switch ($datatype) {
            case 'assetTransforms':
                $records = Craft::$app->assetTransforms->getAllTransforms();
                break;
            case 'categoryGroups':
                $records = Craft::$app->categories->getAllGroups();
                break;
            case 'fields':
                $records = Craft::$app->fields->getAllFields();
                break;
            case 'globalSets':
                $records = Craft::$app->globals->getAllSets();
                break;
            case 'sections':
                $records = Craft::$app->sections->getAllSections();
                break;
            case 'sites':
                $records = Craft::$app->sites->getAllSites();
                break;
            case 'userGroups':
                $records = Craft::$app->userGroups->getAllGroups();
                break;
            case 'volumes':
                $records = Craft::$app->volumes->getAllVolumes();
                break;
            case 'tagGroups':
                $records = Craft::$app->tags->getAllTagGroups();
                break;
        }

        return $records;
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
        Craft::error($message, 'schematic');
    }

    /**
     * Logs a warning message.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     *                              complex data structure, such as array.
     */
    public static function warning($message)
    {
        Craft::warning($message, 'schematic');
    }

    /**
     * Logs an info message.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     *                              complex data structure, such as array.
     */
    public static function info($message)
    {
        Craft::info($message, 'schematic');
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
