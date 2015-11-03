<?php

namespace Craft;

/**
 * Class Schematic_FieldFactoryModel
 */
class Schematic_FieldFactoryModel
{
    /**
     * @return PluginsService
     */
    private function getPluginsService()
    {
        return craft()->plugins;
    }

    /**
     * @param array $fieldDefinition
     * @param FieldModel $field
     * @param string $fieldHandle
     * @param FieldGroupModel|null $group
     * @return FieldModel
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        $schematicFieldModel = $this->getSchematicFieldModel($fieldDefinition['type']);
        $schematicFieldModel->populate($fieldDefinition, $field, $fieldHandle, $group);

        return $field;
    }

    /**
     * @param FieldModel $field
     * @param bool|false $includeContext
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext = true)
    {
        $schematicFieldModel = $this->getSchematicFieldModel($field->type);

        return $schematicFieldModel->getDefinition($field, $includeContext);
    }

    /**
     * @param $fieldType
     * @return Schematic_FieldModel
     */
    private function getSchematicFieldModel($fieldType)
    {
        $fieldModel = new Schematic_FieldModel();
        $classNames = array();
        $customFieldMappings = $this->getPluginsService()->call('registerSchematicFieldModels');

        foreach ($customFieldMappings as $mappings) {
            if (array_key_exists($fieldType, $mappings)) {
                $classNames[] = $mappings[$fieldType];
            }
        }

        $classNames[] = 'Craft\Schematic_' . ucfirst($fieldType) . 'FieldModel';

        foreach($classNames as $className){
            if (class_exists($className)) {
                $tmpFieldModel = new $className;
                if($tmpFieldModel instanceof Schematic_FieldModel){
                    $fieldModel = $tmpFieldModel;
                    break;
                }
            }
        }

        return $fieldModel;
    }
}
