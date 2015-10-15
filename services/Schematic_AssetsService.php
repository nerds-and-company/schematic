<?php

namespace Craft;

class Schematic_AssetsService extends BaseApplicationComponent
{
    /**
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
                return $result->error($assetSource->getAllErrors());
            }
        }

        return $result;
    }

    /**
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
