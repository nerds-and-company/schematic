<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Schematic;
use Symfony\Component\Yaml\Yaml;

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
     * @param string $file    file to write the schema to
     * @param array  $exclude Data to not export
     *
     * @return int
     */
    public function actionIndex(): int
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
     */
    public function exportToYaml($file, $dataTypes): void
    {
        $this->disableLogging();
        $result = [];
        foreach ($dataTypes as $dataTypeHandle) {
            $dataType = $this->module->getDataType($dataTypeHandle);
            if (null == $dataType) {
                continue;
            }

            $mapper = $dataType->getMapperHandle();
            if (!$this->module->checkMapper($mapper)) {
                continue;
            }

            Schematic::info('Exporting '.$dataTypeHandle);
            $records = $dataType->getRecords();
            $result[$dataTypeHandle] = $this->module->$mapper->export($records);
        }

        FileHelper::writeToFile($file, Data::toYaml($result));
    }
}
