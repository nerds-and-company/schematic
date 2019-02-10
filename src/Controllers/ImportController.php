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
     * @throws \Exception
     */
    public function actionIndex(): int
    {
        $dataTypes = $this->getDataTypes();
        $definitions = [];
        $overrideData = Data::parseYamlFile($this->overrideFile);

        $this->disableLogging();

        try {
            // Import from single file.
            if ($this->getStorageType() === self::SINGLE_FILE) {
                $definitions = $this->importDefinitionsFromFile($overrideData);
                $this->importFromDefinitions($dataTypes, $definitions);

                Schematic::info('Loaded schema from '.$this->file);
            }

            // Import from multiple files.
            if ($this->getStorageType() === self::MULTIPLE_FILES) {
                $definitions = $this->importDefinitionsFromDirectory($overrideData);
                $this->importFromDefinitions($dataTypes, $definitions);

                Schematic::info('Loaded schema from '.$this->path);
            }

            return 0;
        } catch (\Exception $e) {
            Schematic::error($e->getMessage());
            return 1;
        }
    }

    /**
     * Import definitions from file
     *
     * @param array $overrideData The overridden data
     * @throws \Exception
     */
    private function importDefinitionsFromFile(array $overrideData): array
    {
        if (!file_exists($this->file)) {
            throw new \Exception('File not found: ' . $this->file);
        }

        // Load data from yam file and replace with override data;
        $definitions = Data::parseYamlFile($this->file);
        return array_replace_recursive($definitions, $overrideData);
    }

    /**
     * Import definitions from directory
     *
     * @param array $overrideData The overridden data
     * @throws \Exception
     */
    private function importDefinitionsFromDirectory(array $overrideData)
    {
        if (!file_exists($this->path)) {
            throw new \Exception('Directory not found: ' . $this->path);
        }

        // Grab all yaml files in the schema directory.
        $schemaFiles = preg_grep('~\.(yml)$~', scandir($this->path));

        // Read contents of each file and add it to the definitions.
        foreach ($schemaFiles as $fileName) {
            $schemaStructure = explode('.', $this->fromSafeFileName($fileName));
            $dataTypeHandle = $schemaStructure[0];
            $recordName = $schemaStructure[1];

            $definition = Data::parseYamlFile($this->path . $fileName);

            // Check if there is data in the override file for the current record.
            if (isset($overrideData[$dataTypeHandle][$recordName])) {
                $definition = array_replace_recursive($definition, $overrideData[$dataTypeHandle][$recordName]);
            }

            $definitions[$dataTypeHandle][$recordName] = $definition;
        }

        return $definitions;
    }

    /**
     * Import from definitions.
     *
     * @param array $dataTypes   The data types to import
     * @param array $definitions The definitions to use
     * @throws \Exception
     */
    private function importFromDefinitions(array $dataTypes, array $definitions)
    {
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
