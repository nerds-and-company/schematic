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
     * Get all global sets
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
            $definition['site'] = $record->site->handle;
            unset($definition['attributes']['tempId']);
            unset($definition['attributes']['uid']);
            unset($definition['attributes']['contentId']);
            unset($definition['attributes']['siteId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        $site = Craft::$app->sites->getSiteByHandle($definition['site']);
        if ($site) {
            $record->siteId = $site->id;
        } else {
            Schematic::warning('Site '.$definition['site']. ' could not be found');
        }
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
