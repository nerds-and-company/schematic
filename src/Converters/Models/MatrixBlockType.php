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
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['fieldId']);
        unset($definition['attributes']['hasFieldErrors']);

        $definition['fields'] = Craft::$app->controller->module->modelMapper->export($record->fieldLayout->getFields());

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $context = 'matrixBlockType:'.$record->id;
        $existingFields = Craft::$app->fields->getAllFields($context);
        $modelMapper = Craft::$app->controller->module->modelMapper;
        $fields = $modelMapper->import($definition['fields'], $existingFields, ['context' => $context], false);
        $record->setFields($fields);

        return Craft::$app->matrix->saveBlockType($record, false);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->matrix->deleteBlockType($record);
    }
}
