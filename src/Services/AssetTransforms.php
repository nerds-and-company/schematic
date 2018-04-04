<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\AssetTransform;

/**
 * Schematic Asset Transforms Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetTransforms extends Base
{
    /**
     * Get all asset transforms
     *
     * @return AssetTransform[]
     */
    protected function getRecords()
    {
        return Craft::$app->assetTransforms->getAllTransforms();
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        $record->setAttributes($definition['attributes']);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->assetTransforms->deleteTransform($record);
    }
}
