<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\CategoryGroup;

/**
 * Schematic Category Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroups extends Base
{
    /**
     * Get all category groups
     *
     * @return CategoryGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->categories->getAllGroups();
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
        return Craft::$app->categories->saveGroup($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->categories->deleteGroup($record);
    }
}
