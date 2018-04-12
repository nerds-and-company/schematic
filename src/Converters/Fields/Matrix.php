<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Base\Field;

/**
 * Schematic Matrix Converter.
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
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);
        $definition['blockTypes'] = Craft::$app->controller->module->modelMapper->export($record->getBlockTypes());

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        if (parent::saveRecord($record, $definition)) {
            if (array_key_exists('blockTypes', $definition)) {
                Craft::$app->controller->module->modelMapper->import($definition['blockTypes'], $record->getBlockTypes(), ['fieldId' => $record->id]);
            }

            return true;
        }

        return false;
    }
}
