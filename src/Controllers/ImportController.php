<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
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
class ImportController extends Base
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
            Schematic::info('Loaded schema from '.$this->file);

            return 0;
        }

        Schematic::info('There was an error loading schema from '.$this->file);

        return 1;
    }

    /**
     * Import from Yaml file.
     *
     * @param string $dataTypes The data types to import
     *
     * @return bool
     *
     * @throws Exception
     */
    private function importFromYaml($dataTypes)
    {
        $this->disableLogging();
        $yaml = file_get_contents($this->file);
        $yamlOverride = null;
        if (file_exists($this->overrideFile)) {
            $yamlOverride = file_get_contents($this->overrideFile);
        }
        $dataModel = Data::fromYaml($yaml, $yamlOverride);

        foreach ($dataTypes as $dataType) {
            $component = Schematic::DATA_TYPES[$dataType]['mapper'];
            if (Craft::$app->controller->module->$component instanceof MapperInterface) {
                Schematic::info('Importing '.$dataType);
                Schematic::$force = $this->force;
                if (is_array($dataModel->$dataType)) {
                    $records = Schematic::getRecords($dataType);
                    Craft::$app->controller->module->$component->import($dataModel->$dataType, $records);
                    if ('fields' == $dataType) {
                        Craft::$app->fields->updateFieldVersion();
                    }
                }
            } else {
                Schematic::error(get_class(Craft::$app->controller->module->$component).' does not implement MapperInterface');
            }
        }

        return true;
    }
}
