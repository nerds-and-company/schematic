<?php

namespace Craft;

/**
 * Class Schematic_MatrixFieldModel
 */
class Schematic_MatrixFieldModel extends Schematic_FieldModel
{

    /**
     * Returns matrix service.
     *
     * @return MatrixService
     */
    private function getMatrixService()
    {
        return craft()->matrix;
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * @param FieldModel $field
     * @param $includeContext
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext)
    {
        $definition = parent::getDefinition($field, $includeContext);
        $definition['blockTypes'] = $this->getBlockTypeDefinitions($field);

        return $definition;
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

        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = array();

            foreach ($blockType->getFields() as $blockTypeField) {
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $fieldFactory->getDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[$blockType->handle] = array(
                'name' => $blockType->name,
                'fields' => $blockTypeFieldDefinitions,
            );
        }

        return $blockTypeDefinitions;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * @param array $fieldDefinition
     * @param FieldModel $field
     * @param string $fieldHandle
     * @param FieldGroupModel|null $group
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);

        /** @var MatrixSettingsModel $settingsModel */
        $settingsModel = $field->getFieldType()->getSettings();
        $settingsModel->setAttributes($fieldDefinition['settings']);
        $settingsModel->setBlockTypes($this->getBlockTypes($fieldDefinition, $field));
        $field->settings = $settingsModel;
    }

    /**
     * Get blocktypes.
     *
     * @param array $fieldDefinition
     * @param FieldModel $field
     *
     * @return mixed
     */
    protected function getBlockTypes(array $fieldDefinition, FieldModel $field)
    {
        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');

        foreach ($fieldDefinition['blockTypes'] as $blockTypeHandle => $blockTypeDef) {
            $blockType = array_key_exists($blockTypeHandle, $blockTypes)
                ? $blockTypes[$blockTypeHandle]
                : new MatrixBlockTypeModel();

            $this->populateBlockType($field, $blockType, $blockTypeDef, $blockTypeHandle);

            $blockTypes[$blockTypeHandle] = $blockType;
        }

        return $blockTypes;
    }

    /**
     * Populate blocktype.
     *
     * @param FieldModel $field
     * @param MatrixBlockTypeModel $blockType
     * @param array $blockTypeDef
     * @param string $blockTypeHandle
     */
    private function populateBlockType(FieldModel $field, MatrixBlockTypeModel $blockType, array $blockTypeDef, $blockTypeHandle)
    {
        $fieldFactory = $this->getFieldFactory();

        $blockType->fieldId = $field->id;
        $blockType->name = $blockTypeDef['name'];
        $blockType->handle = $blockTypeHandle;

        $blockTypeFields = array();
        foreach ($blockType->getFields() as $blockTypeField) {
            $blockTypeFields[$blockTypeField->handle] = $blockTypeField;
        }

        $newBlockTypeFields = array();

        foreach ($blockTypeDef['fields'] as $blockTypeFieldHandle => $blockTypeFieldDef) {
            $blockTypeField = array_key_exists($blockTypeFieldHandle, $blockTypeFields)
                ? $blockTypeFields[$blockTypeFieldHandle]
                : new FieldModel();

            $fieldFactory->populate($blockTypeFieldDef, $blockTypeField, $blockTypeFieldHandle);

            $newBlockTypeFields[] = $blockTypeField;
        }

        $blockType->setFields($newBlockTypeFields);
    }
}
