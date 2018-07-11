<?php

namespace NerdsAndCompany\Schematic\Interfaces;

/**
 * Schematic Data Type Interface.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
interface DataTypeInterface
{
    /**
     * Get mapper component handle.
     *
     * @return string
     */
    public function getMapperHandle(): string;

    /**
     * Get records of this type.
     *
     * @return array
     */
    public function getRecords(): array;

    /**
     * Callback for actions after import.
     * For example to clear caches.
     */
    public function afterImport();
}
