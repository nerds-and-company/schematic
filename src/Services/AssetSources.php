<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\AssetSourceRecord;
use Craft\AssetSourceModel;

/**
 * Schematic Assets Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class AssetSources extends Base
{
    /**
     * @return AssetSourcesService
     */
    private function getAssetSourcesService()
    {
        return Craft::app()->assetSources;
    }

    /**
     * @param $sourceTypeId
     *
     * @return array|mixed|null
     */
    public function getSourceTypeById($sourceTypeId)
    {
        return AssetSourceRecord::model()->findByAttributes(['id' => $sourceTypeId]);
    }

    /**
     * @param $sourceTypeHandle
     *
     * @return array|mixed|null
     */
    public function getSourceTypeByHandle($sourceTypeHandle)
    {
        return AssetSourceRecord::model()->findByAttributes(['handle' => $sourceTypeHandle]);
    }

    /**
     * Import asset source definitions.
     *
     * @param array $assetSourceDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $assetSourceDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Asset Sources'));

        $this->resetCraftAssetSourcesServiceCache();
        $assetSources = Craft::app()->assetSources->getAllSources('handle');

        foreach ($assetSourceDefinitions as $assetSourceHandle => $assetSourceDefinition) {
            $assetSource = array_key_exists($assetSourceHandle, $assetSources)
                ? $assetSources[$assetSourceHandle]
                : new AssetSourceModel();

            unset($assetSources[$assetSourceHandle]);

            $this->populateAssetSource($assetSource, $assetSourceDefinition, $assetSourceHandle);

            if (!Craft::app()->assetSources->saveSource($assetSource)) { // Save assetsource via craft
                $this->addErrors($assetSource->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($assetSources as $assetSource) {
                Craft::app()->assetSources->deleteSourceById($assetSource->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset source.
     *
     * @param AssetSourceModel $assetSource
     * @param string           $assetSourceHandle
     * @param array            $assetSourceDefinition
     *
     * @return AssetSourceModel
     */
    private function populateAssetSource(AssetSourceModel $assetSource, $assetSourceHandle, array $assetSourceDefinition)
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
            $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($assetSourceDefinition['fieldLayout']);
            $assetSource->setFieldLayout($fieldLayout);
        }

        return $assetSource;
    }

    /**
     * Export all asset sources.
     *
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Asset Sources'));

        $assetSources = $this->getAssetSourcesService()->getAllSources();

        $assetSourceDefinitions = [];
        foreach ($assetSources as $assetSource) {
            $assetSourceDefinitions[$assetSource->handle] = $this->getAssetSourceDefinition($assetSource);
        }

        return $assetSourceDefinitions;
    }

    /**
     * @param AssetSourceModel $assetSource
     *
     * @return array
     */
    private function getAssetSourceDefinition(AssetSourceModel $assetSource)
    {
        return [
            'type' => $assetSource->type,
            'name' => $assetSource->name,
            'sortOrder' => $assetSource->sortOrder,
            'settings' => $assetSource->settings,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($assetSource->getFieldLayout()),
        ];
    }

    /**
     * Reset craft fields service cache using reflection.
     */
    private function resetCraftAssetSourcesServiceCache()
    {
        $obj = Craft::app()->categories;
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
