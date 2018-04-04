<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\TagGroup;

/**
 * Schematic TagGroups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TagGroups extends Base
{
    /**
     * Get all tag groups
     *
     * @return TagGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->tags->getAllTagGroups();
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
        return Craft::$app->tags->saveTagGroup($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->tags->deleteTagGroup($record);
    }
}
