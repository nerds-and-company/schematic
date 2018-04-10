<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;

/**
 * Schematic Matrix Block Types Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class MatrixBlockType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['fieldId']);
        unset($definition['attributes']['hasFieldErrors']);

        $fields = Craft::$app->fields->getAllFields('matrixBlockType:'.$record->id);
        $definition['fields'] = Craft::$app->schematic_fields->export($fields);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition)
    {
        $context = 'matrixBlockType:'.$record->id;
        $existingFields = Craft::$app->fields->getAllFields($context);
        $fields = Craft::$app->schematic_fields->import($definition['fields'], $existingFields, [], false);
        $record->setFields($fields);

        return Craft::$app->matrix->saveBlockType($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record)
    {
        return Craft::$app->matrix->deleteBlockType($record);
    }
}
