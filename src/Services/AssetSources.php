<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\AssetSourceRecord;
use Craft\AssetSourceModel;

/**
 * Schematic Asset Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
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
     * @param $sourceId
     *
     * @return array|mixed|null
     */
    public function getSourceById($sourceId)
    {
        return AssetSourceRecord::model()->findByAttributes(['id' => $sourceId]);
    }

    /**
     * @param $sourceHandle
     *
     * @return array|mixed|null
     */
    public function getSourceByHandle($sourceHandle)
    {
        return AssetSourceRecord::model()->findByAttributes(['handle' => $sourceHandle]);
    }

    /**
     * Export all asset sources.
     *
     * @param AssetSourceModel[] $assetSources
     *
     * @return array
     */
    public function export(array $assetSources = [])
    {
        Craft::log(Craft::t('Exporting Asset Sources'));

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
        $fieldLayout = Craft::app()->fields->getLayoutById($assetSource->fieldLayoutId);

        return [
            'type' => $assetSource->type,
            'name' => $assetSource->name,
            'sortOrder' => $assetSource->sortOrder,
            'settings' => $assetSource->settings,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($fieldLayout),
        ];
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
            $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($assetSourceDefinition['fieldLayout']);
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
