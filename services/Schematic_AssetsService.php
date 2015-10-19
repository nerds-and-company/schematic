<?php

namespace Craft;

/**
 * Schematic Assets Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_AssetsService extends BaseApplicationComponent
{
    /**
     * Import asset source definitions.
     *
     * @param array $assetSourceDefinitions
     *
     * @return Schematic_ResultModel
     */
    public function import(array $assetSourceDefinitions)
    {
        $result = new Schematic_ResultModel();

        if (empty($assetSourceDefinitions)) {
            return $result;
        }

        foreach ($assetSourceDefinitions as $assetHandle => $assetSourceDefinition) {
            $assetSource = $this->populateAssetSource($assetHandle, $assetSourceDefinition);

            if (!craft()->assetSources->saveSource($assetSource)) {
                $result->addErrors(array('errors' => $assetSource->getAllErrors()));
            }
        }

        return $result;
    }

    /**
     * Populate asset source.
     *
     * @param string $assetHandle
     * @param array  $assetSourceDefinition
     *
     * @return AssetSourceModel
     */
    private function populateAssetSource($assetHandle, array $assetSourceDefinition)
    {
        $assetSource = $sourceRecord = AssetSourceRecord::model()->findByAttributes(['handle' => $assetHandle]);
        $assetSource = $assetSource ? AssetSourceModel::populateModel($assetSource) : new AssetSourceModel();

        $assetSource->setAttributes(array(
            'handle'       => $assetHandle,
            'type'         => $assetSourceDefinition['type'],
            'name'         => $assetSourceDefinition['name'],
            'sortOrder'    => $assetSourceDefinition['sortOrder'],
            'settings'     => $assetSourceDefinition['settings'],
        ));

        return $assetSource;
    }

    /**
     * Export all asset sources.
     *
     * @return array
     */
    public function export()
    {
        $assetSources = craft()->assetSources->getAllSources();

        $assetSourceDefinitions = array();
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
        return array(
            'type' => $assetSource->type,
            'name' => $assetSource->name,
            'sortOrder' => $assetSource->sortOrder,
            'settings' => $assetSource->settings,
        );
    }
}
