<?php

namespace Craft;

/**
 * Schematic Assets Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_AssetsService extends Schematic_AbstractService
{
    /**
     * Import asset source definitions.
     *
     * @param array $assetSourceDefinitions
     * @param bool  $force
     *
     * @return Schematic_ResultModel
     */
    public function import(array $assetSourceDefinitions, $force = false)
    {
        foreach ($assetSourceDefinitions as $assetHandle => $assetSourceDefinition) {
            $assetSource = $this->populateAssetSource($assetHandle, $assetSourceDefinition);

            if (!craft()->assetSources->saveSource($assetSource)) {
                $this->addErrors($assetSource->getAllErrors());
            }
        }

        return $this->getResultModel();
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
        $assetSource = AssetSourceRecord::model()->findByAttributes(array('handle' => $assetHandle));
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
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = array())
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
