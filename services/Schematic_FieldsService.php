<?php

namespace Craft;

/**
 * Schematic Fields Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_FieldsService extends BaseApplicationComponent
{
    /**
     * Export fields.
     *
     * @param FieldGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups)
    {
        $groupDefinitions = array();

        foreach ($groups as $group) {
            $fieldDefinitions = array();

            foreach ($group->getFields() as $field) {
                $fieldDefinitions[$field->handle] = $this->getFieldDefinition($field);
            }

            $groupDefinitions[$group->name] = $fieldDefinitions;
        }

        return $groupDefinitions;
    }

    /**
     * Get field definition.
     *
     * @param FieldModel $field
     * @param bool       $includeContext
     *
     * @return array
     */
    private function getFieldDefinition(FieldModel $field, $includeContext = true)
    {
        $definition = array(
            'name' => $field->name,
            'required' => $field->required,
            'instructions' => $field->instructions,
            'translatable' => $field->translatable,
            'type' => $field->type,
            'settings' => $field->settings,
        );

        if ($includeContext) {
            $definition['context'] = $field->context;
        }

        switch ($field->type) {
            case 'Entries':
                $definition['settings']['sources'] = $this->getSourceHandles($definition['settings']['sources']);
                break;
            case 'Matrix':
                $definition['blockTypes'] = $this->getBlockTypeDefinitions($field);
                break;
        }

        return $definition;
    }

    /**
     * Get source handles.
     *
     * @param array $sources
     *
     * @return array
     */
    private function getSourceHandles(array $sources)
    {
        $handleSources = [];
        foreach ($sources as $source) {
            $sectionId = explode(':', $source)[1];
            $handleSources[] = craft()->sections->getSectionById($sectionId)->handle;
        }

        return $handleSources;
    }

    /**
     * Get block type definitions.
     *
     * @param FieldModel $field
     *
     * @return array
     */
    private function getBlockTypeDefinitions(FieldModel $field)
    {
        $blockTypeDefinitions = array();

        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = array();

            foreach ($blockType->getFields() as $blockTypeField) {
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $this->getFieldDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[$blockType->handle] = array(
                'name' => $blockType->name,
                'fields' => $blockTypeFieldDefinitions,
            );
        }

        return $blockTypeDefinitions;
    }

    /**
     * Attempt to import fields.
     *
     * @param array $groupDefinitions
     * @param bool  $force            if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import($groupDefinitions, $force = false)
    {
        $result = new Schematic_ResultModel();

        if (empty($groupDefinitions)) {
            // Ignore importing fields.
            return $result;
        }

        $groups = craft()->fields->getAllGroups('name');
        $fields = craft()->fields->getAllFields('handle');

        craft()->content->fieldContext = 'global';
        craft()->content->contentTable = 'content';

        foreach ($groupDefinitions as $groupName => $fieldDefinitions) {
            $group = array_key_exists($groupName, $groups)
                ? $groups[$groupName]
                : new FieldGroupModel();

            unset($groups[$groupName]);

            $group->name = $groupName;

            if (!craft()->fields->saveGroup($group)) {
                $result->addErrors(array('errors' => $group->getAllErrors()));

                continue;
            }

            foreach ($fieldDefinitions as $fieldHandle => $fieldDef) {
                $field = array_key_exists($fieldHandle, $fields)
                    ? $fields[$fieldHandle]
                    : new FieldModel();

                unset($fields[$fieldHandle]);

                $this->populateField($fieldDef, $field, $fieldHandle, $group);

                if (!$field->getFieldType()) {
                    ($field->type == 'Matrix')
                        ? $result->addError("errors", "One of the field's types does not exist. Are you missing a plugin?")
                        : $result->addError("errors", "Field type '$field->type' does not exist. Are you missing a plugin?");

                    break;
                }

                if (!craft()->fields->saveField($field)) {
                    $result->addErrors(array('errors' => $field->getAllErrors()));
                }
            }
        }

        if ($force) {
            foreach ($fields as $field) {
                craft()->fields->deleteFieldById($field->id);
            }
            foreach ($groups as $group) {
                craft()->fields->deleteGroupById($group->id);
            }
        }

        return $result;
    }

    /**
     * Populate blocktype.
     *
     * @param FieldModel           $field
     * @param MatrixBlockTypeModel $blockType
     * @param array                $blockTypeDef
     * @param string               $blockTypeHandle
     */
    private function populateBlockType(FieldModel $field, MatrixBlockTypeModel $blockType, array $blockTypeDef, $blockTypeHandle)
    {
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

            $this->populateField($blockTypeFieldDef, $blockTypeField, $blockTypeFieldHandle);

            $newBlockTypeFields[] = $blockTypeField;
        }

        $blockType->setFields($newBlockTypeFields);
    }

    /**
     * Populate field.
     *
     * @param array           $fieldDefinition
     * @param FieldModel      $field
     * @param string          $fieldHandle
     * @param FieldGroupModel $group
     */
    private function populateField(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        $field->name = $fieldDefinition['name'];
        $field->handle = $fieldHandle;
        $field->required = $fieldDefinition['required'];
        $field->translatable = $fieldDefinition['translatable'];
        $field->instructions = $fieldDefinition['instructions'];
        $field->type = $fieldDefinition['type'];
        $field->settings = $fieldDefinition['settings'];

        if ($group) {
            $field->groupId = $group->id;
        }

        if ($field->type == 'Entries') {
            $settings = $fieldDefinition['settings'];
            $settings['sources'] = $this->getSourceIds($settings['sources']);
            $field->settings = $settings;
        }

        if ($field->type == 'Matrix') {
            $field->settings = $field->getFieldType()->getSettings();
            $field->settings->setAttributes($fieldDefinition['settings']);
            $field->settings->setBlockTypes($this->getBlockTypes($fieldDefinition, $field));
        }
    }

    /**
     * Get source id's.
     *
     * @param array $sourceHandles
     *
     * @return array
     */
    private function getSourceIds($sourceHandles)
    {
        $sections = craft()->sections->getAllSections('handle');
        $sources = [];
        foreach ($sourceHandles as $sourceHandle) {
            if (array_key_exists($sourceHandle, $sections)) {
                $sources[] = 'section:'.$sections[$sourceHandle]->id;
            }
        }

        return $sources;
    }

    /**
     * Get blocktypes.
     *
     * @param array      $fieldDefinition
     * @param FieldModel $field
     *
     * @return mixed
     */
    private function getBlockTypes(array $fieldDefinition, FieldModel $field)
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
     * Get field layout definition.
     *
     * @param FieldLayoutModel $fieldLayout
     *
     * @return array
     */
    public function getFieldLayoutDefinition(FieldLayoutModel $fieldLayout)
    {
        if ($fieldLayout->getTabs()) {
            $tabDefinitions = array();

            foreach ($fieldLayout->getTabs() as $tab) {
                $tabDefinitions[$tab->name] = $this->getFieldLayoutFieldsDefinition($tab->getFields());
            }

            return array('tabs' => $tabDefinitions);
        }

        return array('fields' => $this->getFieldLayoutFieldsDefinition($fieldLayout->getFields()));
    }

    /**
     * Get field layout fields definition.
     *
     * @param FieldLayoutFieldModel[] $fields
     *
     * @return array
     */
    private function getFieldLayoutFieldsDefinition(array $fields)
    {
        $fieldDefinitions = array();

        foreach ($fields as $field) {
            $fieldDefinitions[$field->getField()->handle] = $field->required;
        }

        return $fieldDefinitions;
    }

    /**
     * Attempt to import a field layout.
     *
     * @param array $fieldLayoutDef
     *
     * @return FieldLayoutModel
     */
    public function getFieldLayout(array $fieldLayoutDef)
    {
        $layoutFields = array();
        $requiredFields = array();

        if (array_key_exists('tabs', $fieldLayoutDef)) {
            foreach ($fieldLayoutDef['tabs'] as $tabName => $tabDef) {
                $layoutTabFields = $this->getPrepareFieldLayout($tabDef);
                $requiredFields = array_merge($requiredFields, $layoutTabFields['required']);
                $layoutFields[$tabName] = $layoutTabFields['fields'];
            }
        } elseif (array_key_exists('fields', $fieldLayoutDef)) {
            $layoutTabFields = $this->getPrepareFieldLayout($fieldLayoutDef);
            $requiredFields = $layoutTabFields['required'];
            $layoutFields = $layoutTabFields['fields'];
        }

        $fieldLayout = craft()->fields->assembleLayout($layoutFields, $requiredFields);
        $fieldLayout->type = ElementType::Entry;

        return $fieldLayout;
    }

    /**
     * Get a prepared fieldLayout for the craft assembleLayout function.
     *
     * @param array $fieldLayoutDef
     *
     * @return array
     */
    private function getPrepareFieldLayout(array $fieldLayoutDef)
    {
        $layoutFields = array();
        $requiredFields = array();

        foreach ($fieldLayoutDef as $fieldHandle => $required) {
            $field = craft()->fields->getFieldByHandle($fieldHandle);

            if ($field instanceof FieldModel) {
                $layoutFields[] = $field->id;

                if ($required) {
                    $requiredFields[] = $field->id;
                }
            }
        }

        return array(
            'fields' => $layoutFields,
            'required' => $requiredFields,
        );
    }
}
