<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use yii\console\Controller;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Base Controller.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Base extends Controller
{
    public $file = 'config/schema.yml';
    public $overrideFile = 'config/override.yml';
    public $exclude;
    public $include;

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function options($actionID): array
    {
        return ['file', 'overrideFile', 'include', 'exclude'];
    }

    /**
     * Get the datatypes to import and/or export.
     *
     * @return array
     */
    protected function getDataTypes(): array
    {
        $dataTypes = array_keys($this->module->dataTypes);

        // If include is specified.
        if (null !== $this->include) {
            $dataTypes = $this->applyIncludes($dataTypes);
        }

        // If there are exclusions.
        if (null !== $this->exclude) {
            $dataTypes = $this->applyExcludes($dataTypes);
        }

        //Import fields and usergroups again after all sources have been imported
        if (array_search('fields', $dataTypes) && count($dataTypes) > 1) {
            $dataTypes[] = 'fields';
            $dataTypes[] = 'userGroups';
        }

        return $dataTypes;
    }

    /**
     * Apply given includes.
     *
     * @param array $dataTypes
     *
     * @return array
     */
    protected function applyIncludes($dataTypes): array
    {
        $inclusions = explode(',', $this->include);
        // Find any invalid data to include.
        $invalidIncludes = array_diff($inclusions, $dataTypes);
        if (count($invalidIncludes) > 0) {
            $errorMessage = 'WARNING: Invalid include(s)';
            $errorMessage .= ': '.implode(', ', $invalidIncludes).'.'.PHP_EOL;
            $errorMessage .= ' Valid inclusions are '.implode(', ', $dataTypes);

            // Output an error message outlining what invalid exclusions were specified.
            Schematic::warning($errorMessage);
        }
        // Remove any explicitly included data types from the list of data types to export.
        return array_intersect($dataTypes, $inclusions);
    }

    /**
     * Apply given excludes.
     *
     * @param array $dataTypes
     *
     * @return array
     */
    protected function applyExcludes(array $dataTypes): array
    {
        $exclusions = explode(',', $this->exclude);
        // Find any invalid data to exclude.
        $invalidExcludes = array_diff($exclusions, $dataTypes);
        if (count($invalidExcludes) > 0) {
            $errorMessage = 'WARNING: Invalid exlude(s)';
            $errorMessage .= ': '.implode(', ', $invalidExcludes).'.'.PHP_EOL;
            $errorMessage .= ' Valid exclusions are '.implode(', ', $dataTypes);

            // Output an error message outlining what invalid exclusions were specified.
            Schematic::warning($errorMessage);
        }
        // Remove any explicitly excluded data types from the list of data types to export.
        return array_diff($dataTypes, $exclusions);
    }

    /**
     * Disable normal logging (to stdout) while running console commands.
     *
     * @TODO: Find a less hacky way to solve this
     */
    protected function disableLogging()
    {
        if (Craft::$app->log) {
            Craft::$app->log->targets = [];
        }
    }
}
