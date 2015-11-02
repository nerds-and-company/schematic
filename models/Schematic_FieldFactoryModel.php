<?php

namespace Craft;

class Schematic_FieldFactoryModel
{
    public function buildField(array $fieldDefinition)
    {

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
