<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Export Controller.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ExportController extends Base
{
    /**
     * Exports the Craft datamodel.
     *
     * @return int
     * @throws \yii\base\ErrorException
     */
    public function actionIndex(): int
    {
        $this->disableLogging();

        $configurations = [];
        foreach ($this->getDataTypes() as $dataTypeHandle) {
            $dataType = $this->module->getDataType($dataTypeHandle);
            if (null == $dataType) {
                continue;
            }

            $mapper = $dataType->getMapperHandle();
            if (!$this->module->checkMapper($mapper)) {
                continue;
            }

            $records = $dataType->getRecords();
            $configurations[$dataTypeHandle] = $this->module->$mapper->export($records);
        }

        // Load override file.
        $overrideData = [];
        if (file_exists($this->overrideFile)) {
            // Parse data in the overrideFile if available.
            $overrideData = Data::parseYamlFile($this->overrideFile);
        }

        // Export the configuration to a single file.
        if ($this->getStorageType() === self::SINGLE_FILE) {
            $configurations = array_replace_recursive($configurations, $overrideData);
            FileHelper::writeToFile($this->file, Data::toYaml($configurations));
            Schematic::info('Exported schema to '.$this->file);
        }

        // Export the configuration to multiple yaml files.
        if ($this->getStorageType() === self::MULTIPLE_FILES) {
            // Create export directory if it doesn't exist.
            if (!file_exists($this->path)) {
                mkdir($this->path, 2775, true);
            }

            foreach ($configurations as $dataTypeHandle => $configuration) {
                Schematic::info('Exporting '.$dataTypeHandle);
                foreach ($configuration as $recordName => $records) {
                    // Check if there is data in the override file for the current record.
                    if (isset($overrideData[$dataTypeHandle][$recordName])) {
                        $records = array_replace_recursive($records, $overrideData[$dataTypeHandle][$recordName]);
                    }

                    // Export records to file.
                    $fileName = $this->toSafeFileName($dataTypeHandle.'.'.$recordName.'.yml');
                    FileHelper::writeToFile($this->path.$fileName, Data::toYaml($records));
                    Schematic::info('Exported '.$recordName.' to '.$fileName);
                }
            }
        }

        return 0;
    }
}
