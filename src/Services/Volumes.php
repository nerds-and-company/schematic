<?php

namespace NerdsAndCompany\Schematic\Services;

use \Craft;
use craft\base\VolumeInterface;

/**
 * Schematic Asset Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Volumes extends Base
{
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all asset transforms
     *
     * @return VolumeInterface[]
     */
    protected function getRecords()
    {
        return Craft::$app->volumes->getAllVolumes();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Import asset source definitions.
     *
     * @param array $assetSourceDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import($force = false, array $assetSourceDefinitions = null)
    {
        Craft::info('Importing Asset Sources', 'schematic');

        $this->resetCraftAssetSourcesServiceCache();
        $assetSources = $this->getAssetSourcesService()->getAllSources('handle');

        foreach ($assetSourceDefinitions as $assetSourceHandle => $assetSourceDefinition) {
            $assetSource = array_key_exists($assetSourceHandle, $assetSources)
                ? $assetSources[$assetSourceHandle]
                : new AssetSourceModel();

            unset($assetSources[$assetSourceHandle]);

            $this->populateAssetSource($assetSource, $assetSourceDefinition, $assetSourceHandle);

            if (!$this->getAssetSourcesService()->saveSource($assetSource)) { // Save assetsource via craft
                $this->addErrors($assetSource->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($assetSources as $assetSource) {
                $this->getAssetSourcesService()->deleteSourceById($assetSource->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset source.
     *
     * @param AssetSourceModel $assetSource
     * @param array            $assetSourceDefinition
     * @param string           $assetSourceHandle
     *
     * @return AssetSourceModel
     */
    private function populateAssetSource(AssetSourceModel $assetSource, array $assetSourceDefinition, $assetSourceHandle)
    {
        $defaultAssetSourceSettings = array(
            'publicURLs' => true,
        );

        $assetSource->setAttributes([
            'handle' => $assetSourceHandle,
            'type' => $assetSourceDefinition['type'],
            'name' => $assetSourceDefinition['name'],
            'sortOrder' => $assetSourceDefinition['sortOrder'],
            'settings' => array_merge($defaultAssetSourceSettings, $assetSourceDefinition['settings']),
        ]);

        if (array_key_exists('fieldLayout', $assetSourceDefinition)) {
            $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($assetSourceDefinition['fieldLayout']);
            $assetSource->setFieldLayout($fieldLayout);
        }

        return $assetSource;
    }

    /**
     * Reset craft fields service cache using reflection.
     */
    private function resetCraftAssetSourcesServiceCache()
    {
        $obj = $this->getAssetSourcesService();
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllSources')) {
            $refProperty = $refObject->getProperty('_fetchedAllSources');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_sourcesById')) {
            $refProperty = $refObject->getProperty('_sourcesById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
    }
}
