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
            $definition['siteSettings'] = [];
            foreach ($record->getSiteSettings() as $siteSetting) {
                $attributes = $siteSetting->attributes;
                unset($attributes['sectionId']);
                unset($attributes['id']);
                $definition['siteSettings'][] = $attributes;
            }
        }
        if ($record instanceof EntryType) {
            unset($definition['attributes']['sectionId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        if ($record instanceof Section) {
            $siteSettings = [];
            foreach ($definition['siteSettings'] as $siteSettingDefinition) {
                $siteSettings[] = new Section_SiteSettings($siteSettingDefinition);
            }
            $record->setSiteSettings($siteSettings);
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
