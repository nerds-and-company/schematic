<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\GlobalSet;
use craft\base\Model;

/**
 * Schematic Globals Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSets extends Base
{
    /**
     * Get all asset transforms
     *
     * @return GlobalSet[]
     */
    protected function getRecords()
    {
        return Craft::$app->globals->getAllSets();
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @param array $definition
     * @return boolean
     */
    protected function saveRecord(Model $record, array $definition)
    {
        $record->setAttributes($definition['attributes']);
        return Craft::$app->globals->saveSet($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->globals->deleteSet($record);
    }
}
