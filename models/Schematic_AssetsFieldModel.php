<?php

namespace Craft;

require_once __DIR__.'/Schematic_FieldModel.php';

class Schematic_AssetsFieldModel extends Schematic_FieldModel
{
    /**
     * @param FieldModel $field
     * @param $includeContext
     *
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext)
    {
        $definition = parent::getDefinition($field, $includeContext);
        $settings = $definition['settings'];

        $defaultUploadLocationSourceId = $settings['defaultUploadLocationSource'];
        $defaultUploadLocationSource = craft()->schematic_assets->getSourceTypeById($defaultUploadLocationSourceId);
        $settings['defaultUploadLocationSource'] = $defaultUploadLocationSource ?  $defaultUploadLocationSource->handle : '';

        $singleUploadLocationSourceId = $settings['singleUploadLocationSource'];
        $singleUploadLocationSource = craft()->schematic_assets->getSourceTypeById($singleUploadLocationSourceId);
        $settings['singleUploadLocationSource'] = $singleUploadLocationSource ? $singleUploadLocationSource->handle : '';

        $definition['settings'] = $settings;

        return $definition;
    }

    /**
     * @param array                $fieldDefinition
     * @param FieldModel           $field
     * @param string               $fieldHandle
     * @param FieldGroupModel|null $group
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);
        
        $settings = $fieldDefinition['settings'];
        $defaultUploadLocationSourceId = $settings['defaultUploadLocationSource'];
        $defaultUploadLocationSource = craft()->schematic_assets->getSourceTypeByHandle($defaultUploadLocationSourceId);
        $settings['defaultUploadLocationSource'] = $defaultUploadLocationSource ?  $defaultUploadLocationSource->id : '';

        $singleUploadLocationSourceId = $settings['singleUploadLocationSource'];
        $singleUploadLocationSource = craft()->schematic_assets->getSourceTypeByHandle($singleUploadLocationSourceId);
        $settings['singleUploadLocationSource'] = $singleUploadLocationSource ? $singleUploadLocationSource->id : '';

        $field->settings = $settings;
    }
}
