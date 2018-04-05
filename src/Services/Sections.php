<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\Section;
use craft\models\EntryType;
use craft\models\Section_SiteSettings;

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

    /**
     * {@inheritdoc}
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

        if ($record instanceof Section_SiteSettings) {
            unset($definition['attributes']['sectionId']);
            unset($definition['attributes']['siteId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        if ($record instanceof Section) {
            if (Craft::$app->sections->saveSection($record)) {
                parent::import($definition['entryTypes'], $record->getEntryTypes(), ['sectionId' => $record->id]);
                return true;
            }
        }

        if ($record instanceof EntryType) {
            return Craft::$app->sections->saveEntryType($record);
        }

        return false;
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        if ($record instanceof Section) {
            return Craft::$app->sections->deleteSection($record);
        }
        if ($record instanceof EntryType) {
            return Craft::$app->sections->deleteEntryType($record);
        }
        return false;
    }
}
