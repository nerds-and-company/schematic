<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use Craft\Exception;
use craft\helpers\FileHelper;
use Symfony\Component\Yaml\Yaml;
use yii\base\Component as BaseComponent;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Schematic extends BaseComponent
{
    const DATA_TYPES = [
        'assetSources',
        'assetTransforms',
        'fields',
        // 'plugins',
        'sections',
        'globalSets',
        'userGroups',
        // 'users',
        'categoryGroups',
        'tagGroups',
        'elementIndexSettings',
        // 'pluginData',
    ];

    // /**
    //  * Returns data from import model or default.
    //  *
    //  * @param array  $data
    //  * @param string $handle
    //  * @param array  $default
    //  *
    //  * @return array
    //  */
    // private function getPluginData(array $data, $handle, array $default = [])
    // {
    //     return (array_key_exists($handle, $data) && !is_null($data[$handle])) ? $data[$handle] : $default;
    // }

    // /**
    //  * Import from Yaml file.
    //  *
    //  * @param string $file
    //  * @param string $override
    //  * @param bool   $force if set to true items not included in import will be deleted
    //  * @param string $dataTypes The data types to import
    //  *
    //  * @return Result
    //  * @throws Exception
    //  */
    // public function importFromYaml($file, $override = null, $force = false, $dataTypes = 'all')
    // {
    //     Craft::$app->config->maxPowerCaptain();
    //     Craft::$app->setComponent('userSession', $this);
    //
    //     $yaml = FileHelper::getFileContents($file);
    //     $yaml_override = FileHelper::getFileContents($override);
    //     $dataModel = Data::fromYaml($yaml, $yaml_override);
    //
    //     return $this->importDataModel($dataModel, $force, $dataTypes);
    // }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return Result
     */
    public function exportToYaml($file, $dataTypes)
    {
        $result = [];
        foreach ($dataTypes as $dataType) {
            $component = 'schematic_'.$dataType;
            $result[$dataType] = Craft::$app->$component->export();
        }

        $yaml = Yaml::dump($result, 10);
        if (!FileHelper::writeToFile($file, $yaml)) {
            Craft::error('error', "Failed to write contents to \"$file\"", 'schematic');
        }

        return true;
    }
}
