<?php
namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft;
use yii\base\Behavior;
use yii\console\Controller;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic FieldLayout Behavior.
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
    public $overrideFile = 'craft/config/override.yml';
    public $exclude;
    public $include;

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function options($actionID)
    {
        return ['file', 'override_file', 'include', 'exclude'];
    }

    /**
     * Get the datatypes to import and/or export
     *
     * @return array
     */
    protected function getDataTypes()
    {
        $dataTypes = Schematic::DATA_TYPES;

        // If include is specified.
        if ($this->include !== null) {
            $dataTypes = $this->applyIncludes($dataTypes);
        }

        // If there are exclusions.
        if ($this->exclude !== null) {
            $dataTypes = $this->applyExcludes($dataTypes);
        }
        return $dataTypes;
    }

    /**
     * Apply given includes
     *
     * @param  array $dataTypes
     * @return array
     */
    protected function applyIncludes($dataTypes)
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
     * Apply given excludes
     *
     * @param  array $dataTypes
     * @return array
     */
    protected function applyExcludes(array $dataTypes)
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
}
