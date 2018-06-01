<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;
use craft\models\CategoryGroup_SiteSettings;

/**
 * Schematic Category Groups Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroup extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record instanceof CategoryGroup_SiteSettings) {
            unset($definition['attributes']['groupId']);
            unset($definition['attributes']['siteId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $this->resetCraftSiteServiceSiteIdsCache();

        return Craft::$app->categories->saveGroup($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->categories->deleteGroupById($record->id);
    }

    /**
     * Reset craft site service site ids cache using reflection.
     */
    private function resetCraftSiteServiceSiteIdsCache()
    {
        $obj = Craft::$app->sites;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_sitesById')) {
            $refProperty1 = $refObject->getProperty('_sitesById');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, null);
            $obj->init(); // reload sites
        }
    }
}
