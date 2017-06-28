<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\Craft;
use Craft\BaseModel;
use Craft\FieldModel;
use Craft\FieldGroupModel;
use Craft\MatrixBlockTypeModel;

/**
 * Schematic Matrix Field Model.
 *
 * A schematic field model for mapping matrix data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class MatrixField extends Field
{
    /**
     * Returns matrix service.
     *
     * @return MatrixService
     */
    private function getMatrixService()
    {
        return Craft::app()->matrix;
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * @param FieldModel $field
     * @param $includeContext
     *
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
        $blockTypeDefinitions = [];

        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = [];

            foreach ($blockType->getFields() as $blockTypeField) {
                $schematicFieldModel = $fieldFactory->build($blockTypeField->type);
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $schematicFieldModel->getDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[$blockType->handle] = [
                'name' => $blockType->name,
                'fields' => $blockTypeFieldDefinitions,
            ];
        }

        return $blockTypeDefinitions;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * @param array                $fieldDefinition
     * @param FieldModel           $field
     * @param string               $fieldHandle
     * @param FieldGroupModel|null $group
     * @param bool                 $force
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null, $force = false)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);

        /** @var MatrixSettingsModel $settingsModel */
        $settingsModel = $field->getFieldType()->getSettings();
        $settingsModel->setAttributes($fieldDefinition['settings']);
        $settingsModel->setBlockTypes($this->getBlockTypes($fieldDefinition, $field, $force));
        $field->settings = $settingsModel;
    }

    /**
     * Get blocktypes.
     *
     * @param array      $fieldDefinition
     * @param FieldModel $field
     * @param bool       $force
     *
     * @return mixed
     */
    protected function getBlockTypes(array $fieldDefinition, FieldModel $field, $force = false)
    {
        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id, 'handle');

        //delete old blocktypes if they are missing from the definition.
        if ($force) {
            foreach ($blockTypes as $key => $value) {
                if (!array_key_exists($key, $fieldDefinition['blockTypes'])) {
                    unset($blockTypes[$key]);
                }
            }
        }

        foreach ($fieldDefinition['blockTypes'] as $blockTypeHandle => $blockTypeDef) {
            $blockType = array_key_exists($blockTypeHandle, $blockTypes)
                ? $blockTypes[$blockTypeHandle]
                : new MatrixBlockTypeModel();

            $blockType->fieldId = $field->id;
            $blockType->name = $blockTypeDef['name'];
            $blockType->handle = $blockTypeHandle;

            $this->populateBlockType($blockType, $blockTypeDef);

            $blockTypes[$blockTypeHandle] = $blockType;
        }

        return $blockTypes;
    }

    /**
     * Populate blocktype.
     *
     * @param BaseModel $blockType
     * @param array     $blockTypeDef
     */
    protected function populateBlockType(BaseModel $blockType, array $blockTypeDef)
    {
        $fieldFactory = $this->getFieldFactory();

        $blockTypeFields = [];
        foreach ($blockType->getFields() as $blockTypeField) {
            $blockTypeFields[$blockTypeField->handle] = $blockTypeField;
        }

        $newBlockTypeFields = [];

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
