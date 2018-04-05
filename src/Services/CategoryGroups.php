<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;

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
     * {@inheritdoc}
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        if ($record instanceof CategoryGroup) {
            $definition['siteSettings'] = [];
            foreach ($record->getSiteSettings() as $siteSetting) {
                $attributes = $siteSetting->attributes;
                unset($attributes['id']);
                $definition['siteSettings'][] = $attributes;
            }
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        if (array_key_exists('siteSettings', $definition)) {
            $siteSettings = [];
            foreach ($definition['siteSettings'] as $siteSettingDefinition) {
                $siteSettings[] = new CategoryGroup_SiteSettings($siteSettingDefinition);
            }
            $record->setSiteSettings($siteSettings);
        }

        return Craft::$app->categories->saveGroup($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->categories->deleteGroupById($record->id);
    }
}
