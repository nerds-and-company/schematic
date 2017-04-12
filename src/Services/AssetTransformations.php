<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\AssetTransformModel;
use Craft\AssetTransformRecord;
use Craft\AssetTransformsService;
use Craft\Craft;

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
class AssetTransformations extends Base
{
    /**
     * @return AssetTransformsService
     */
    private function getAssetTransformationsService()
    {
        return Craft::app()->assetTransforms;
    }

    /**
     * @param $transformRecordId
     *
     * @return array|mixed|null
     */
    public function getSourceTypeById($transformRecordId)
    {
        return AssetTransformRecord::model()->findByAttributes(['id' => $transformRecordId]);
    }

    /**
     * @param $transformTypeHandle
     *
     * @return array|mixed|null
     */
    public function getSourceTypeByHandle($transformTypeHandle)
    {
        return AssetTransformRecord::model()->findByAttributes(['handle' => $transformTypeHandle]);
    }

    /**
     * Import asset transformation definitions.
     *
     * @param array $assetTransformDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $assetTransformDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Asset Transformations'));

        foreach ($assetTransformDefinitions as $assetHandle => $assetTransformDefinition) {
            $assetTransform = $this->populateAssetTransform($assetHandle, $assetTransformDefinition);

            if (!Craft::app()->assetTransforms->saveTransform($assetTransform)) {
                $this->addErrors($assetTransform->getAllErrors());
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset source.
     *
     * @param string $assetHandle
     * @param array  $assetTransformDefinition
     *
     * @return AssetTransformModel
     */
    private function populateAssetTransform($assetHandle, array $assetTransformDefinition)
    {
        $assetTransform = AssetTransformRecord::model()->findByAttributes(['handle' => $assetHandle]);
        $assetTransform = $assetTransform ? AssetTransformModel::populateModel($assetTransform) : new AssetTransformModel();
        $defaultAssetTransformSettings = array(
        );

        $assetTransform->setAttributes([
            'handle' => $assetHandle,
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
     * Export all asset sources.
     *
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Asset Transforms'));

        $assetTransforms = $this->getAssetTransformationsService()->getAllTransforms();

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
            'name'                => $assetTransform->name,
            'width'               => $assetTransform->width,
            'height'              => $assetTransform->height,
            'format'              => $assetTransform->format,
            'dimensionChangeTime' => $assetTransform->dimensionChangeTime,
            'mode'                => $assetTransform->mode,
            'position'            => $assetTransform->position,
            'quality'             => $assetTransform->quality,
        ];
    }
}
