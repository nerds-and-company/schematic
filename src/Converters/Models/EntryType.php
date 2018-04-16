<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;

/**
 * Schematic Entry Types Converter.
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
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['sectionId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        return Craft::$app->sections->saveEntryType($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->sections->deleteEntryType($record);
    }
}
