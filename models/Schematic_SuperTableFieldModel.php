<?php

namespace Craft;

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
class Schematic_SuperTableFieldModel extends Schematic_MatrixFieldModel
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
     * @param array $fieldDefinition
     * @param FieldModel $field
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

            $this->populateBlockType($field, $blockType, $blockTypeDef);

            $blockTypes[$index] = $blockType;
            $index++;
        }

        return $blockTypes;
    }

    /**
     * @param FieldModel $field
     * @param SuperTable_BlockTypeModel $blockType
     * @param array $blockTypeDef
     */
    private function populateBlockType(FieldModel $field, SuperTable_BlockTypeModel $blockType, array $blockTypeDef)
    {
        $fieldFactory = $this->getFieldFactory();

        $blockType->fieldId = $field->id;

        $blockTypeFields = array();
        foreach ($blockType->getFields() as $blockTypeField) {
            $blockTypeFields[$blockTypeField->handle] = $blockTypeField;
        }

        $newBlockTypeFields = array();

        foreach ($blockTypeDef['fields'] as $blockTypeFieldHandle => $blockTypeFieldDef) {
            $blockTypeField = array_key_exists($blockTypeFieldHandle, $blockTypeFields)
                ? $blockTypeFields[$blockTypeFieldHandle]
                : new FieldModel();

            $schematicFieldModel = $fieldFactory->build($blockTypeFieldDef['type']);
            $schematicFieldModel->populate($blockTypeFieldDef, $blockTypeField, $blockTypeFieldHandle);

            $newBlockTypeFields[] = $blockTypeField;
        }

        $blockType->setFields($newBlockTypeFields);
    }
}
