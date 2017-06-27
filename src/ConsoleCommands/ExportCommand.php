<?php

namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft\Craft;
use Craft\BaseCommand as Base;
use NerdsAndCompany\Schematic\Services\Schematic;

/**
 * Schematic Export Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ExportCommand extends Base
{
    /**
     * Exports the Craft datamodel.
     *
     * @param string $file    file to write the schema to
     * @param array  $exclude Data to not export
     *
     * @return int
     */
    public function actionIndex($file = 'craft/config/schema.yml', array $exclude = null)
    {
        $dataTypes = Schematic::getExportableDataTypes();

        // If there are data exclusions.
        if ($exclude !== null) {
            // Find any invalid data to exclude.
            $invalidExcludes = array_diff($exclude, $dataTypes);

            // If any invalid exclusions were specified.
            if (count($invalidExcludes) > 0) {
                $errorMessage = 'Invalid exlude';

                if (count($invalidExcludes) > 1) {
                    $errorMessage .= 's';
                }

                $errorMessage .= ': '.implode(', ', $invalidExcludes).'.';
                $errorMessage .= ' Valid exclusions are '.implode(', ', $dataTypes);

                // Output an error message outlining what invalid exclusions were specified.
                echo "\n".$errorMessage."\n\n";

                return 1;
            }

            // Remove any explicitly excluded data types from the list of data types to export.
            $dataTypes = array_diff($dataTypes, $exclude);
        }

        Craft::app()->schematic->exportToYaml($file, $dataTypes);

        Craft::log(Craft::t('Exported schema to {file}', ['file' => $file]));

        return 0;
    }
}
