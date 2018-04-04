<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\GlobalSet;
use craft\base\Model;

/**
 * Schematic Globals Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSets extends Base
{
    /**
     * Get all asset transforms
     *
     * @return GlobalSet[]
     */
    protected function getRecords()
    {
        return Craft::$app->globals->getAllSets();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        if ($record instanceof GlobalSet) {
            unset($definition['attributes']['tempId']);
            unset($definition['attributes']['uid']);
            unset($definition['attributes']['contentId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        return Craft::$app->globals->saveSet($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->globals->deleteSet($record);
    }
}
