<?php

namespace Craft;

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
class Schematic_PositionSelectFieldModel extends Schematic_FieldModel
{
    /**
     * @param array $fieldDefinition
     * @param FieldModel $field
     * @param string $fieldHandle
     * @param FieldGroupModel|null $group
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);

        $options = array();
        $settings = $fieldDefinition['settings'];
        foreach ($settings['options'] as $option) {
            $options[$option] = $option;
        }
        $settings['options'] = $options;
        $field->settings = $settings;
    }
}
