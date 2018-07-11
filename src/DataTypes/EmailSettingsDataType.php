<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic EmailSettings DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class EmailSettingsDataType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getMapperHandle(): string
    {
        return 'emailSettingsMapper';
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        return [];
    }
}
