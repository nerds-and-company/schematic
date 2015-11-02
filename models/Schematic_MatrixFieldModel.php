<?php

namespace Craft;

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
    private function getBlockTypeDefinitions(FieldModel $field)
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
}
