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
class Schematic_FieldsService extends Schematic_AbstractService
{
    /**
     * @var FieldModel[]
     */
    private $fields = array();

    /**
     * @var FieldGroupModel[]
     */
    private $groups = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->groups = $this->getFieldsService()->getAllGroups('name');
        $this->fields = $this->getFieldsService()->getAllFields('handle');
    }

    /**
     * Returns fields service.
     *
     * @return FieldsService
     */
    private function getFieldsService()
    {
        return craft()->fields;
    }

    /**
     * Returns content service.
     *
     * @return ContentService
     */
    private function getContentService()
    {
        return craft()->content;
    }

    /**
     * Returns matrix service.
     *
     * @return MatrixService
     */
    private function getMatrixService()
    {
        return craft()->matrix;
    }

    /**
     * Export fields.
     *
     * @param FieldGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups = array())
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

        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id);
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
     * Save field group.
     *
     * @param FieldGroupModel $group
     *
     * @throws Exception
     */
    private function saveFieldGroupModel(FieldGroupModel $group)
    {
        if (!$this->getFieldsService()->saveGroup($group)) {
            $this->addErrors($group->getAllErrors());

            throw new Exception('Failed to save group');
        }
    }

    /**
     * Save field.
     *
     * @param FieldModel $field
     *
     * @throws \Exception
     */
    private function saveFieldModel(FieldModel $field)
    {
        $this->validateFieldModel($field); // Validate field
        if (!$this->getFieldsService()->saveField($field)) {
            $this->addErrors($field->getAllErrors());

            throw new Exception('Failed to save field');
        }
    }

    /**
     * Removes fields that where not imported.
     */
    private function deleteFields()
    {
        $fieldsService = $this->getFieldsService();
        foreach ($this->fields as $field) {
            $fieldsService->deleteFieldById($field->id);
        }
    }

    /**
     * Removes groups that where not imported.
     */
    private function deleteGroups()
    {
        $fieldsService = $this->getFieldsService();
        foreach ($this->groups as $group) {
            $fieldsService->deleteGroupById($group->id);
        }
    }

    /**
     * Removes fields and groups that where not imported.
     */
    private function deleteFieldsAndGroups()
    {
        $this->deleteFields();
        $this->deleteGroups();
    }

    /**
     * Creates new or updates existing group model.
     *
     * @param string $group
     *
     * @return FieldGroupModel
     */
    private function createFieldGroupModel($group)
    {
        $groupModel = (array_key_exists($group, $this->groups) ? $this->groups[$group] : new FieldGroupModel());
        $groupModel->name = $group;

        $this->saveFieldGroupModel($groupModel);

        return $groupModel;
    }

    /**
     * @param string $field
     *
     * @return FieldModel
     */
    private function getFieldModel($field)
    {
        return (array_key_exists($field, $this->fields) ? $this->fields[$field] : new FieldModel());
    }

    /**
     * Validates field type, throw error when it's incorrect.
     *
     * @param FieldModel $field
     *
     * @throws \Exception
     */
    private function validateFieldModel(FieldModel $field)
    {
        if (!$field->getFieldType()) {
            $fieldType = $field->type;
            ($fieldType == 'Matrix')
                ? $this->addError("One of the field's types does not exist. Are you missing a plugin?")
                : $this->addError("Field type '$fieldType' does not exist. Are you missing a plugin?");

            throw new Exception('Failed to save field');
        }
    }

    /**
     * Import field group fields.
     *
     * @param array           $fieldDefinitions
     * @param FieldGroupModel $group
     *
     * @throws \Exception
     */
    private function importFields(array $fieldDefinitions, FieldGroupModel $group)
    {
        foreach ($fieldDefinitions as $fieldHandle => $fieldDef) {
            $field = $this->getFieldModel($fieldHandle);

            $this->populateField($fieldDef, $field, $fieldHandle, $group);

            $this->saveFieldModel($field);
        }
    }

    /**
     * Unset group and field data else $force flag will delete it.
     *
     * @param string $name
     * @param array  $definitions
     */
    private function unsetData($name, array $definitions)
    {
        if (array_key_exists($name, $this->groups)) {
            unset($this->groups[$name]);
            foreach ($definitions as $handle => $definition) {
                unset($this->fields[$handle]);
            }
        }
    }

    /**
     * Attempt to import fields.
     *
     * @param array $groupDefinitions
     * @param bool  $force            if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import(array $groupDefinitions, $force = false)
    {
        if (!empty($groupDefinitions)) {
            $contentService = $this->getContentService();

            $contentService->fieldContext = 'global';
            $contentService->contentTable = 'content';

            foreach ($groupDefinitions as $name => $fieldDefinitions) {
                try {
                    $this->beginTransaction();

                    $group = $this->createFieldGroupModel($name);

                    $this->importFields($fieldDefinitions, $group);

                    $this->commitTransaction();
                } catch (\Exception $e) {
                    $this->rollbackTransaction();

                    $this->addError($e->getMessage());
                }

                $this->unsetData($name, $fieldDefinitions);
            }

            if ($force) { // Remove not imported data
                $this->deleteFieldsAndGroups();
            }
        }

        return $this->getResultModel();
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
    private function populateField(
        array $fieldDefinition,
        FieldModel $field,
        $fieldHandle,
        FieldGroupModel $group = null
    ) {
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
