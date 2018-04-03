<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\base\Field;
use craft\models\FieldLayout;

/**
 * Schematic Fields Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Fields extends Base
{
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all field groups
     *
     * @return FieldGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->fields->getAllGroups();
    }

    /**
     * Get all field definitions per group
     *
     * @return array
     */
    public function export(array $records = null)
    {
        $fieldGroups = $records ?: $this->getRecords();
        $result = [];
        foreach ($fieldGroups as $group) {
            $fields = $group->getFields();
            if (count($fields) > 0) {
                $result[$group->name] = parent::export($group->getFields());
            }
        }
        return $result;
    }

    /**
     * Get section definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $attributes = parent::getRecordDefinition($record);
        if ($record instanceof Field) {
            unset($attributes['groupId']);
            unset($attributes['layoutId']);
            unset($attributes['tabId']);
        }

        return $attributes;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Attempt to import fields.
     *
     * @param array $groupDefinitions
     * @param bool  $force            if set to true items not in the import will be deleted
     *
     * @return Result
     */
    public function import($force = false, array $groupDefinitions = null)
    {
        Craft::info('Importing Fields', 'schematic');

        if (!empty($groupDefinitions)) {
            $this->setGlobalContext();
            $this->groups = Craft::$app->fields->getAllGroups('name');
            $this->fields = Craft::$app->fields->getAllFields('handle');

            foreach ($groupDefinitions as $name => $fieldDefinitions) {
                try {
                    $this->beginTransaction();

                    $group = $this->createFieldGroupModel($name);

                    $this->importFields($fieldDefinitions, $group, $force);

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
     * Save field group.
     *
     * @param FieldGroupModel $group
     *
     * @throws Exception
     */
    private function saveFieldGroupModel(FieldGroupModel $group)
    {
        if (!Craft::$app->fields->saveGroup($group)) {
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
        if ($field->context === 'global') {
            $this->setGlobalContext();
        }
        if (!Craft::$app->fields->saveField($field)) {
            $this->addErrors($field->getAllErrors());

            throw new Exception('Failed to save field');
        }
    }

    /**
     * Removes fields that where not imported.
     */
    private function deleteFields()
    {
        $fieldsService = Craft::$app->fields;
        foreach ($this->fields as $field) {
            $fieldsService->deleteFieldById($field->id);
        }
    }

    /**
     * Removes groups that where not imported.
     */
    private function deleteGroups()
    {
        $fieldsService = Craft::$app->fields;
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
        return array_key_exists($field, $this->fields) ? $this->fields[$field] : new FieldModel();
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
     * @param bool            $force
     *
     * @throws \Exception
     */
    private function importFields(array $fieldDefinitions, FieldGroupModel $group, $force = false)
    {
        $fieldFactory = $this->getFieldFactory();

        foreach ($fieldDefinitions as $fieldHandle => $fieldDef) {
            $field = $this->getFieldModel($fieldHandle);
            $schematicFieldModel = $fieldFactory->build($fieldDef['type']);

            if ($schematicFieldModel->getDefinition($field, true) === $fieldDef) {
                Craft::info('Skipping `{name}`, no changes detected', ['name' => $field->name], 'schematic');
                continue;
            }

            Craft::info('Importing `{name}`', ['name' => $fieldDef['name']], 'schematic');

            $schematicFieldModel->populate($fieldDef, $field, $fieldHandle, $group, $force);
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
     * Set global field context.
     */
    private function setGlobalContext()
    {
        Craft::$app->content->fieldContext = 'global';
        Craft::$app->content->contentTable = 'content';
    }
}
