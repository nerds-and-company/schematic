<?php

namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Import Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ImportCommand extends Base
{
    public $force = false;

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['force']);
    }

    /**
     * Imports the Craft datamodel.
     *
     * @return int
     */
    public function actionIndex()
    {
        if (!file_exists($this->file)) {
            Schematic::error('File not found: '.$this->file);
            return 1;
        }

        $dataTypes = $this->getDataTypes();
        if ($this->importFromYaml($dataTypes)) {
            Schematic::info('Loaded schema from '. $this->file);
            return 0;
        }

        Schematic::info('There was an error loading schema from '. $this->file);
        return 1;
    }

    /**
     * Import from Yaml file.
     *
     * @param string $dataTypes The data types to import
     *
     * @return boolean
     * @throws Exception
     */
    private function importFromYaml($dataTypes)
    {
        $yaml = file_get_contents($this->file);
        $yamlOverride = null;
        if (file_exists($this->overrideFile)) {
            $yamlOverride = file_get_contents($this->overrideFile);
        }
        $dataModel = Data::fromYaml($yaml, $yamlOverride);

        foreach (array_keys($dataTypes) as $dataType) {
            $component = 'schematic_'.$dataType;
            if (Craft::$app->$component instanceof MappingInterface) {
                Schematic::info('Importing '.$dataType);
                Schematic::$force = $this->force;
                Craft::$app->$component->import($dataModel->$dataType);
            } else {
                Schematic::error(get_class(Craft::$app->$component).' does not implement MappingInterface');
            }
        }

        return true;
    }
}
