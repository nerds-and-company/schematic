<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\Craft;
use Craft\FieldModel;
use Craft\FieldGroupModel;
use Craft\AssetSourceRecord;

/**
 * Schematic Assets Field Model.
 *
 * A schematic field model for mapping asset data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class AssetsField extends Field
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

        if ($settings && array_key_exists('defaultUploadLocationSource', $settings)) {
            $defaultUploadLocationSourceId = $settings['defaultUploadLocationSource'];
            $defaultUploadLocationSource = Craft::app()->assetSources->getSourceById($defaultUploadLocationSourceId);
            $settings['defaultUploadLocationSource'] = $defaultUploadLocationSource ? $defaultUploadLocationSource->handle : '';
        }

        if ($settings && array_key_exists('singleUploadLocationSource', $settings)) {
            $singleUploadLocationSourceId = $settings['singleUploadLocationSource'];
            $singleUploadLocationSource = Craft::app()->assetSources->getSourceById($singleUploadLocationSourceId);
            $settings['singleUploadLocationSource'] = $singleUploadLocationSource ? $singleUploadLocationSource->handle : '';
        }

        $definition['settings'] = $settings;

        return $definition;
    }

    /**
     * @param array                $fieldDefinition
     * @param FieldModel           $field
     * @param string               $fieldHandle
     * @param FieldGroupModel|null $group
     * @param bool                 $force
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null, $force = false)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group, $force);

        $settings = $field->settings;

        if ($settings && array_key_exists('defaultUploadLocationSource', $settings)) {
            $defaultUploadLocationSourceId = $settings['defaultUploadLocationSource'];
            $defaultUploadLocationSource = AssetSourceRecord::model()->findByAttributes(['handle' => $defaultUploadLocationSourceId]);
            $settings['defaultUploadLocationSource'] = $defaultUploadLocationSource ? $defaultUploadLocationSource->id : '';
        }

        if ($settings && array_key_exists('singleUploadLocationSource', $settings)) {
            $singleUploadLocationSourceId = $settings['singleUploadLocationSource'];
            $singleUploadLocationSource = AssetSourceRecord::model()->findByAttributes(['handle' => $singleUploadLocationSourceId]);
            $settings['singleUploadLocationSource'] = $singleUploadLocationSource ? $singleUploadLocationSource->id : '';
        }

        $field->settings = $settings;
    }
}
