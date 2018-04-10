<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;
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
class CategoryGroup extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record)
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
    public function saveRecord(Model $record, array $definition)
    {
        return Craft::$app->categories->saveGroup($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record)
    {
        return Craft::$app->categories->deleteGroupById($record->id);
    }
}
