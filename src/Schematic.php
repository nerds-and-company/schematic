<?php

namespace NerdsAndCompany\Schematic;

use Craft;

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
    const DATA_TYPES = [
        'sites' => Services\Sites::class,
        'volumes' => Services\Volumes::class,
        'assetTransforms' => Services\ModelProcessor::class,
        'fields' => Services\Fields::class,
        'plugins' => Services\Plugins::class,
        'sections' => Services\Sections::class,
        'globalSets' => Services\GlobalSets::class,
        'userGroups' => Services\UserGroups::class,
        'users' => Services\Users::class,
        'categoryGroups' => Services\CategoryGroups::class,
        'tagGroups' => Services\TagGroups::class,
        'elementIndexSettings' => Services\ElementIndexSettings::class,
    ];

    public static function getRecords(string $datatype)
    {
        $records = [];
        switch ($datatype) {
            case 'assetTransforms':
                $records = Craft::$app->assetTransforms->getAllTransforms();
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
}
