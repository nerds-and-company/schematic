<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\Exception;
use Craft\FieldModel;
use Craft\FieldGroupModel;
use Craft\FieldLayoutModel;
use Craft\ElementType;
use NerdsAndCompany\Schematic\Models\FieldFactory;

/**
 * Schematic Fields Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Fields extends Base
{
    /**
     * @var FieldModel[]
     */
    private $fields = [];

    /**
     * @var FieldGroupModel[]
     */
    private $groups = [];

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @return FieldFactory
     */
    public function getFieldFactory()
    {
        return isset($this->fieldFactory) ? $this->fieldFactory : new FieldFactory();
    }

    //==============================================================================================================
    //===============================================  SERVICES  ===================================================
    //==============================================================================================================

    /**
     * Returns fields service.
     *
     * @return FieldsService
     */
    private function getFieldsService()
    {
        return Craft::app()->fields;
    }

    /**
     * Returns content service.
     *
     * @return ContentService
     */
    private function getContentService()
    {
        return Craft::app()->content;
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Export fields.
     *
     * @param FieldGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups = [])
    {
        Craft::log(Craft::t('Exporting Fields'));

        $groupDefinitions = [];

        foreach ($groups as $group) {
            $fieldDefinitions = [];

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
     *
     * @return array
     */
    private function getFieldDefinition(FieldModel $field)
    {
        $fieldFactory = $this->getFieldFactory();
        $schematicFieldModel = $fieldFactory->build($field->type);
        $definition = $schematicFieldModel->getDefinition($field, true);

        return $definition;
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
    public function import(array $groupDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Fields'));

        if (!empty($groupDefinitions)) {
            $contentService = $this->getContentService();

            $contentService->fieldContext = 'global';
            $contentService->contentTable = 'content';

            $this->groups = $this->getFieldsService()->getAllGroups('name');
            $this->fields = $this->getFieldsService()->getAllFields('handle');

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
        $fieldFactory = $this->getFieldFactory();

        foreach ($fieldDefinitions as $fieldHandle => $fieldDef) {
            $field = $this->getFieldModel($fieldHandle);
            $schematicFieldModel = $fieldFactory->build($fieldDef['type']);

            if ($schematicFieldModel->getDefinition($field, true) === $fieldDef ) {
                Craft::log(Craft::t('Skipping `{name}`, no changes detected', ['name' => $field->name]));
                continue;
            }

            Craft::log(Craft::t('Importing `{name}`', ['name' => $fieldDef['name']]));

            $schematicFieldModel->populate($fieldDef, $field, $fieldHandle, $group);
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

    //==============================================================================================================
    //=============================================  FIELD LAYOUT  =================================================
    //==============================================================================================================

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
            $tabDefinitions = [];

            foreach ($fieldLayout->getTabs() as $tab) {
                $tabDefinitions[$tab->name] = $this->getFieldLayoutFieldsDefinition($tab->getFields());
            }

            return ['tabs' => $tabDefinitions];
        }

        return ['fields' => $this->getFieldLayoutFieldsDefinition($fieldLayout->getFields())];
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
        $fieldDefinitions = [];

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
        $layoutFields = [];
        $requiredFields = [];

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

        $fieldLayout = Craft::app()->fields->assembleLayout($layoutFields, $requiredFields);
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
        $layoutFields = [];
        $requiredFields = [];

        foreach ($fieldLayoutDef as $fieldHandle => $required) {
            $field = Craft::app()->fields->getFieldByHandle($fieldHandle);

            if ($field instanceof FieldModel) {
                $layoutFields[] = $field->id;

                if ($required) {
                    $requiredFields[] = $field->id;
                }
            }
        }

        return [
            'fields' => $layoutFields,
            'required' => $requiredFields,
        ];
    }
}
