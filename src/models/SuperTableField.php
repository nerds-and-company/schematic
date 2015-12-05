<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\FieldModel;
use Craft\SuperTable_BlockTypeModel;

/**
 * Schematic Super Table Field Model.
 *
 * A schematic field model for mapping super table data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class SuperTableField extends MatrixField
{
    /**
     * @return SuperTableService
     */
    private function getSuperTableService()
    {
        return craft()->superTable;
    }

    /**
     * Get block type definitions.
     *
     * @param FieldModel $field
     *
     * @return array
     */
    protected function getBlockTypeDefinitions(FieldModel $field)
    {
        $fieldFactory = $this->getFieldFactory();
        $blockTypeDefinitions = array();

        /** @var SuperTable_BlockTypeModel[] $blockTypes */
        $blockTypes = $this->getSuperTableService()->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = array();

            foreach ($blockType->getFields() as $blockTypeField) {
                $schematicFieldModel = $fieldFactory->build($blockTypeField->type);
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $schematicFieldModel->getDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[] = array(
                'fields' => $blockTypeFieldDefinitions,
            );
        }

        return $blockTypeDefinitions;
    }

    /**
     * @param array      $fieldDefinition
     * @param FieldModel $field
     *
     * @return SuperTable_BlockTypeModel[]
     */
    protected function getBlockTypes(array $fieldDefinition, FieldModel $field)
    {
        $blockTypes = $this->getSuperTableService()->getBlockTypesByFieldId($field->id);

        $index = 0;
        foreach ($fieldDefinition['blockTypes'] as $blockTypeDef) {
            $blockType = array_key_exists($index, $blockTypes)
                ? $blockTypes[$index]
                : new SuperTable_BlockTypeModel();

            $blockType->fieldId = $field->id;

            $this->populateBlockType($blockType, $blockTypeDef);

            $blockTypes[$index] = $blockType;
            $index++;
        }

        return $blockTypes;
    }
}
