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
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PositionSelectField extends Field
{
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

        $options = [];
        $settings = $fieldDefinition['settings'];
        foreach ($settings['options'] as $option) {
            $options[$option] = $option;
        }
        $settings['options'] = $options;
        $field->settings = $settings;
    }
}
