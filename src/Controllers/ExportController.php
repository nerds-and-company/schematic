<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
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
        $this->disableLogging();
        $result = [];
        foreach ($dataTypes as $dataTypeHandle) {
            $dataTypeClass = $this->module->dataTypes[$dataTypeHandle];
            $dataType = new $dataTypeClass();
            if (!$dataType instanceof DataTypeInterface) {
                Schematic::error($dataTypeClass.' does not implement DataTypeInterface');
                continue;
            }

            $mapper = $dataType->getMapperHandle();
            if (!$this->module->$mapper instanceof MapperInterface) {
                Schematic::error(get_class($this->module->$mapper).' does not implement MapperInterface');
                continue;
            }

            Schematic::info('Exporting '.$dataTypeHandle);
            $records = $dataType->getRecords();
            $result[$dataTypeHandle] = $this->module->$mapper->export($records);
        }

        FileHelper::writeToFile($file, Data::toYaml($result));

        return 0;
    }
}
