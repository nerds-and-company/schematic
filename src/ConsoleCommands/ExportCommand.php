<?php

namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;
use Symfony\Component\Yaml\Yaml;

/**
 * Schematic Export Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
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
    public function actionIndex()
    {
        $dataTypes = $this->getDataTypes();

        $this->exportToYaml($this->file, $dataTypes);
        Schematic::info('Exported schema to '.$this->file);

        return 0;
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return int
     */
    public function exportToYaml($file, $dataTypes)
    {
        $result = [];
        foreach (array_keys($dataTypes) as $dataType) {
            $component = 'schematic_'.$dataType;
            if (Craft::$app->$component instanceof MappingInterface) {
                Schematic::info('Exporting '.$dataType);
                $result[$dataType] = Craft::$app->$component->export();
            } else {
                Schematic::error(get_class(Craft::$app->$component).' does not implement MappingInterface');
            }
        }

        $yaml = Yaml::dump($result, 10);
        if (!FileHelper::writeToFile($file, $yaml)) {
            Schematic::error('error', "Failed to write contents to \"$file\"");
            return 1;
        }

        return 0;
    }
}
