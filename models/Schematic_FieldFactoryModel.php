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
     * @param $fieldType
     * @return Schematic_FieldModel
     */
    public function build($fieldType)
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
