<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\AssetTransform;

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
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all asset transforms
     *
     * @return AssetTransform[]
     */
    protected function getRecords()
    {
        return Craft::$app->assetTransforms->getAllTransforms();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

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
        Craft::info('Importing Asset Transforms', 'schematic');

        $this->resetCraftAssetTransformsServiceCache();
        $assetTransforms = $this->getAssetTransformsService()->getAllTransforms('handle');

        foreach ($assetTransformDefinitions as $assetTransformHandle => $assetTransformDefinition) {
            $assetTransform = array_key_exists($assetTransformHandle, $assetTransforms)
                ? $assetTransforms[$assetTransformHandle]
                : new AssetTransformModel();

            unset($assetTransforms[$assetTransformHandle]);

            $this->populateAssetTransform($assetTransform, $assetTransformDefinition, $assetTransformHandle);

            if (!$this->getAssetTransformsService()->saveTransform($assetTransform)) { // Save asset transform via craft
                $this->addErrors($assetTransform->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($assetTransforms as $assetTransform) {
                $this->getAssetTransformsService()->deleteTransform($assetTransform->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset transform.
     *
     * @param AssetTransformModel $assetTransform
     * @param array               $assetTransformDefinition
     * @param string              $assetTransformHandle
     *
     * @return AssetTransformModel
     */
    private function populateAssetTransform(AssetTransformModel $assetTransform, array $assetTransformDefinition, $assetTransformHandle)
    {
        $assetTransform->setAttributes([
            'handle' => $assetTransformHandle,
            'name' => $assetTransformDefinition['name'],
            'width' => $assetTransformDefinition['width'],
            'height' => $assetTransformDefinition['height'],
            'format' => $assetTransformDefinition['format'],
            'mode' => $assetTransformDefinition['mode'],
            'position' => $assetTransformDefinition['position'],
            'quality' => $assetTransformDefinition['quality'],
        ]);

        return $assetTransform;
    }

    /**
     * Reset craft fields service cache using reflection.
     */
    private function resetCraftAssetTransformsServiceCache()
    {
        $obj = $this->getAssetTransformsService();
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllTransforms')) {
            $refProperty = $refObject->getProperty('_fetchedAllTransforms');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_transformsByHandle')) {
            $refProperty = $refObject->getProperty('_transformsByHandle');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
    }
}
