<?php

namespace NerdsAndCompany\Schematic\Interfaces;

use craft\base\Model;

/**
 * Schematic Converter Interface.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
interface ConverterInterface
{
    /**
     * Save a record.
     *
     * @param Model $record
     * @param array $definition
     *
     * @return bool
     */
    public function saveRecord(Model $record, array $definition): bool;

    /**
     * Delete a record.
     *
     * @param Model $record
     *
     * @return bool
     */
    public function deleteRecord(Model $record): bool;

    /**
     * Gets the record's key to index by.
     *
     * @param Model $record
     *
     * @return string
     */
    public function getRecordIndex(Model $record): string;

    /**
     * Get single record definition.
     *
     * @param Model $record
     *
     * @return array
     */
    public function getRecordDefinition(Model $record): array;

    /**
     * Set record attributes from definition.
     *
     * @param Model $record
     * @param array $definition
     * @param array $defaultAttributes to also use
     */
    public function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes);
}
