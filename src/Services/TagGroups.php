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
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        return Craft::$app->tags->saveTagGroup($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->tags->deleteTagGroupById($record->id);
    }
}
