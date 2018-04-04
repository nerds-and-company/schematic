<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\fields\PlainText;
use craft\models\FieldLayout;

/**
 * Schematic Fields Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Fields extends Base
{
    /**
     * @TODO: export to schema file
     * @var Field
     */
    protected $recordClass = PlainText::class;

    /**
     * Get all field groups
     *
     * @return FieldGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->fields->getAllFields();
    }

    /**
     * Get section definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $attributes = parent::getRecordDefinition($record);
        if ($record instanceof Field) {
            $attributes = $record->group->name;
            unset($attributes['groupId']);
            unset($attributes['layoutId']);
            unset($attributes['tabId']);
        }

        return $attributes;
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function saveRecord(Model $record)
    {
        return Craft::$app->fields->saveField($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->fields->deleteField($record);
    }
}
