<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;

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
class EntryType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['sectionId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition)
    {
        return Craft::$app->sections->saveEntryType($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record)
    {
        return Craft::$app->sections->deleteEntryType($record);
    }
}
