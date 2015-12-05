<?php

namespace NerdsAndCompany\Schematic\Models;

/**
 * Schematic Field Factory Model.
 *
 * Provides a schematic field model for mapping data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class FieldFactory
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
     *
     * @return Field
     */
    public function build($fieldType)
    {
        $fieldModel = new Field();
        $classNames = array();
        $customFieldMappings = $this->getPluginsService()->call('registerSchematicFieldModels');

        foreach ($customFieldMappings as $mappings) {
            if (array_key_exists($fieldType, $mappings)) {
                $classNames[] = $mappings[$fieldType];
            }
        }

        $classNames[] = ucfirst($fieldType).'Field';

        foreach ($classNames as $className) {
            if (class_exists($className)) {
                $tmpFieldModel = new $className();
                if ($tmpFieldModel instanceof Field) {
                    $fieldModel = $tmpFieldModel;
                    break;
                }
            }
        }

        return $fieldModel;
    }
}
