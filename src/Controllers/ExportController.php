<?php

namespace NerdsAndCompany\Schematic\Controllers;

use Craft;
use craft\helpers\FileHelper;
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
        foreach ($dataTypes as $dataType) {
            $component = Schematic::DATA_TYPES[$dataType]['mapper'];
            if (Craft::$app->controller->module->$component instanceof MapperInterface) {
                Schematic::info('Exporting '.$dataType);
                $records = Schematic::getRecords($dataType);
                $result[$dataType] = Craft::$app->controller->module->$component->export($records);
            } else {
                Schematic::error(get_class(Craft::$app->controller->module->$component).' does not implement MapperInterface');
            }
        }

        FileHelper::writeToFile($file, Data::toYaml($result));

        return 0;
    }
}
