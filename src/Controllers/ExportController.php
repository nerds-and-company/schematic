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
        $result = [];
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

        // Parse data in the overrideFile if available.
        $overrideData = Data::parseYamlFile($this->overrideFile);

        // Create export directory if it doesn't exist.
        if (!file_exists($this->path)) {
            mkdir($this->path, 2775, true);
        }

        // Export the configuration to multiple yaml files.
        foreach ($configurations as $dataTypeHandle => $configuration) {
            Schematic::info('Exporting '.$dataTypeHandle);
            foreach ($configuration as $recordName => $records) {
                // Check if there is data in the override file for the current record.
                if (isset($overrideData[$dataTypeHandle][$recordName])) {
                    $records = array_replace_recursive($records, $overrideData[$dataTypeHandle][$recordName]);
                }

                // Export records to file.
                $fileName = $this->toSafeFileName($dataTypeHandle . '.' . $recordName . '.yml');
                FileHelper::writeToFile($this->path . $fileName, Data::toYaml($records));
                Schematic::info('Exported ' . $recordName . ' to ' . $fileName);
            }
        }

        return 0;
    }
}
