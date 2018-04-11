<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Base\Field;

/**
 * Schematic Globals Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Matrix extends Field
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        $definition['blockTypes'] = Craft::$app->schematic_fields->export($record->getBlockTypes());

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition)
    {
        if (parent::saveRecord($record, $definition)) {
            if (array_key_exists('blockTypes', $definition)) {
                Craft::$app->schematic_fields->import($definition['blockTypes'], $record->getBlockTypes(), ['fieldId' => $record->id]);
            }

            return true;
        }

        return false;
    }
}
