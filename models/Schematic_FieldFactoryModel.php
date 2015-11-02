<?php

namespace Craft;

class Schematic_FieldFactoryModel
{

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
        $className = 'Craft\Schematic_' . ucfirst($fieldType) . 'FieldModel';

        if (class_exists($className)) {
            return new $className;
        }

        return new Schematic_FieldModel();
    }
}
