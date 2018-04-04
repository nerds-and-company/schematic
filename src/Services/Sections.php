<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\Section;
use craft\models\EntryType;

/**
 * Schematic Sections.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Sections extends Base
{
    /**
     * Get all section records
     *
     * @return Section[]
     */
    protected function getRecords()
    {
        return Craft::$app->sections->getAllSections();
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get section definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        if ($record instanceof Section) {
            $definition['entryTypes'] = $this->export($record->getEntryTypes());
        }
        if ($record instanceof EntryType) {
            unset($definition['attributes']['sectionId']);
        }

        return $definition;
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
        return Craft::$app->sections->saveSection($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->sections->deleteSection($record);
    }
}
