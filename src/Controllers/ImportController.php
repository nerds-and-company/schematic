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
        if (!file_exists($this->path)) {
            Schematic::error('Directory not found: ' . $this->path);

            return 1;
        }

        $dataTypes = $this->getDataTypes();
        $this->importFromYaml($dataTypes);
        Schematic::info('Loaded schema from '.$this->path);

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

        $yamlOverride = null;
        if (file_exists($this->overrideFile)) {
            $yamlOverride = file_get_contents($this->overrideFile);
        }

        // Grab all yaml files in the schema directory.
        $schemaFiles = preg_grep('~\.(yml)$~', scandir($this->path));

        // Read contents of each file and add it to the definitions.
        foreach ($schemaFiles as $fileName) {
            $schemaStructure = explode('.', $this->fromSafeFileName($fileName));
            $dataTypeHandle = $schemaStructure[0];
            $recordName = $schemaStructure[1];

            $contents = file_get_contents($this->path . $fileName);

            $definitions[$dataTypeHandle][$recordName] = Data::fromYaml($contents, $yamlOverride);
        }

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
