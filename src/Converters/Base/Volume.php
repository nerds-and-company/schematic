<?php

namespace NerdsAndCompany\Schematic\Converters\Base;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Volume Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Volume extends Base
{
    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        return Craft::$app->volumes->saveVolume($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->volumes->deleteVolume($record);
    }
}
