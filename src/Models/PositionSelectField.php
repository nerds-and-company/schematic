<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\FieldModel;
use Craft\FieldGroupModel;

/**
 * Schematic Position Select Field Model.
 *
 * A schematic field model for mapping position select data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class PositionSelectField extends Field
{
    /**
     * @param array                $fieldDefinition
     * @param FieldModel           $field
     * @param string               $fieldHandle
     * @param FieldGroupModel|null $group
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);

        $options = [];
        $settings = $fieldDefinition['settings'];
        foreach ($settings['options'] as $option) {
            $options[$option] = $option;
        }
        $settings['options'] = $options;
        $field->settings = $settings;
    }
}
