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
        $this->resetCraftMatrixServiceBlockTypesCache();

        // Get existing fields by block type handle
        $existingFields = [];
        $existingBlockTypes = Craft::$app->matrix->getBlockTypesByFieldId($record->fieldId);
        foreach ($existingBlockTypes as $existingBlockType) {
            if ($existingBlockType->handle == $definition['attributes']['handle']) {
                $existingFields = $existingBlockType->getFields();
                break;
            }
        }

        // Set the content table for this matrix block
        $originalContentTable = Craft::$app->content->contentTable;
        $matrixField = Craft::$app->fields->getFieldById($record->fieldId);
        $contentTable = Craft::$app->matrix->getContentTableName($matrixField);
        Craft::$app->content->contentTable = $contentTable;

        // Get the matrix block fields from the definition
        $modelMapper = Craft::$app->controller->module->modelMapper;
        $fields = $modelMapper->import($definition['fields'], $existingFields, [], false);
        $record->setFields($fields);

        // Save the matrix block
        $result = Craft::$app->matrix->saveBlockType($record, false);

        // Restore the content table to what it was before
        Craft::$app->content->contentTable = $originalContentTable;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->matrix->deleteBlockType($record);
    }

    /**
     * Reset craft matrix service block types cache using reflection.
     */
    private function resetCraftMatrixServiceBlockTypesCache()
    {
        $obj = Craft::$app->matrix;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllBlockTypesForFieldId')) {
            $refProperty1 = $refObject->getProperty('_fetchedAllBlockTypesForFieldId');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, false);
        }
    }
}
