<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;

/**
 * Schematic Asset Transforms Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetTransforms extends Base
{
    /**
     * @return AssetTransformsService
     */
    private function getAssetTransformsService()
    {
        return Craft::app()->assetTransforms;
    }

    /**
     * @param $transformId
     *
     * @return array|mixed|null
     */
    public function getTransformById($transformId)
    {
        return AssetTransformRecord::model()->findByAttributes(['id' => $transformId]);
    }

    /**
     * @param $transformHandle
     *
     * @return array|mixed|null
     */
    public function getTransformByHandle($transformHandle)
    {
        return AssetTransformRecord::model()->findByAttributes(['handle' => $transformHandle]);
    }

    /**
     * Import asset transform definitions.
     *
     * @param array $assetTransformDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $assetTransformDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Asset Transforms'));

        foreach ($assetTransformDefinitions as $assetTransformHandle => $assetTransformDefinition) {
            $assetTransform = $this->populateAssetTransform($assetTransformHandle, $assetTransformDefinition);

            if (!Craft::app()->assetTransforms->saveTransform($assetTransform)) {
                $this->addErrors($assetTransform->getAllErrors());
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset transform.
     *
     * @param string $assetTransformHandle
     * @param array  $assetTransformDefinition
     *
     * @return AssetTransformModel
     */
    private function populateAssetSource($assetTransformHandle, array $assetTransformDefinition)
    {
        $assetTransform = AssetTransformRecord::model()->findByAttributes(['handle' => $assetTransformHandle]);
        $assetTransform = $assetTransform ? AssetTransformModel::populateModel($assetTransform) : new AssetTransformModel();

        $assetTransform->setAttributes([
            'handle' => $assetTransformHandle,
            'name' => $assetTransformDefinition['name'],
            'width' => $assetTransformDefinition['width'],
            'height' => $assetTransformDefinition['height'],
            'format' => $assetTransformDefinition['format'],
            'dimensionChangeTime' => $assetTransformDefinition['dimensionChangeTime'],
            'mode' => $assetTransformDefinition['mode'],
            'position' => $assetTransformDefinition['position'],
            'quality' => $assetTransformDefinition['quality'],
        ]);

        return $assetTransform;
    }

    /**
     * Export all asset transforms.
     *
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Asset Transforms'));

        $assetTransforms = $this->getAssetTransformsService()->getAllTransforms();

        $assetTransformDefinitions = [];
        foreach ($assetTransforms as $assetTransform) {
            $assetTransformDefinitions[$assetTransform->handle] = $this->getAssetTransformDefinition($assetTransform);
        }

        return $assetTransformDefinitions;
    }

    /**
     * @param AssetTransformModel $assetTransform
     *
     * @return array
     */
    private function getAssetTransformDefinition(AssetTransformModel $assetTransform)
    {
        return [
            'name' => $assetTransform->name,
            'width' => $assetTransform->width,
            'height' => $assetTransform->height,
            'format' => $assetTransform->format,
            'dimensionChangeTime' => $assetTransform->dimensionChangeTime,
            'mode' => $assetTransform->mode,
            'position' => $assetTransform->position,
            'quality' => $assetTransform->quality,
        ];
    }
}
