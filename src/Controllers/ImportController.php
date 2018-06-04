<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Schematic;
use craft\errors\WrongEditionException;

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
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['force']);
    }

    /**
     * Imports the Craft datamodel.
     *
     * @return int
     */
    public function actionIndex(): int
    {
        if (!file_exists($this->file)) {
            Schematic::error('File not found: '.$this->file);

            return 1;
        }

        $dataTypes = $this->getDataTypes();
        $this->importFromYaml($dataTypes);
        Schematic::info('Loaded schema from '.$this->file);

        return 0;
    }

    /**
     * Import from Yaml file.
     *
     * @param array $dataTypes The data types to import
     *
     * @throws Exception
     */
    private function importFromYaml(array $dataTypes)
    {
        $this->disableLogging();
        $yaml = file_get_contents($this->file);
        $yamlOverride = null;
        if (file_exists($this->overrideFile)) {
            $yamlOverride = file_get_contents($this->overrideFile);
        }
        $definitions = Data::fromYaml($yaml, $yamlOverride);

        foreach ($dataTypes as $dataTypeHandle) {
            $dataType = $this->module->getDataType($dataTypeHandle);
            if (null == $dataType) {
                continue;
            }

            $mapper = $dataType->getMapperHandle();
            if (!$this->module->checkMapper($mapper)) {
                continue;
            }

            Schematic::info('Importing '.$dataTypeHandle);
            Schematic::$force = $this->force;
            if (array_key_exists($dataTypeHandle, $definitions) && is_array($definitions[$dataTypeHandle])) {
                $records = $dataType->getRecords();
                try {
                    $this->module->$mapper->import($definitions[$dataTypeHandle], $records);
                    $dataType->afterImport();
                } catch (WrongEditionException $e) {
                    Schematic::error('Craft Pro is required for datatype '.$dataTypeHandle);
                }
            }
        }
    }
}
