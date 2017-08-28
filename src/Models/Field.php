<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\Craft;
use Craft\FieldModel;
use Craft\FieldGroupModel;

/**
 * Schematic Field Model.
 *
 * A schematic field model for mapping data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Field
{
    /**
     * @return FieldFactory
     */
    protected function getFieldFactory()
    {
        return Craft::app()->schematic_fields->getFieldFactory();
    }

    /**
     * @param FieldModel $field
     * @param $includeContext
     *
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext)
    {
        $definition = [
            'name' => $field->name,
            'required' => $field->required,
            'instructions' => $field->instructions,
            'translatable' => $field->translatable,
            'type' => $field->type,
            'settings' => $field->settings,
        ];

        if ($includeContext) {
            $definition['context'] = $field->context;
        }

        if (isset($definition['settings']['sources'])) {
            $definition['settings']['sources'] = Craft::app()->schematic_sources->getMappedSources($field->type, $definition['settings']['sources'], 'id', 'handle');
        }

        if (isset($definition['settings']['source'])) {
            $definition['settings']['source'] = Craft::app()->schematic_sources->getSource($field->type, $definition['settings']['source'], 'id', 'handle');
        }

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
        $field->name = $fieldDefinition['name'];
        $field->handle = $fieldHandle;
        $field->required = $fieldDefinition['required'];
        $field->translatable = $fieldDefinition['translatable'];
        $field->instructions = $fieldDefinition['instructions'];
        $field->type = $fieldDefinition['type'];
        $field->settings = $fieldDefinition['settings'];

        if ($group) {
            $field->groupId = $group->id;
        }

        if (isset($fieldDefinition['context'])) {
            $field->context = $fieldDefinition['context'];
        }

        if (isset($fieldDefinition['settings']['sources'])) {
            $settings = $fieldDefinition['settings'];
            $settings['sources'] = Craft::app()->schematic_sources->getMappedSources($field->type, $settings['sources'], 'handle', 'id');
            $field->settings = $settings;
        }

        if (isset($fieldDefinition['settings']['source'])) {
            $settings = $fieldDefinition['settings'];
            $settings['source'] = Craft::app()->schematic_sources->getSource($field->type, $settings['source'], 'handle', 'id');
            $field->settings = $settings;
        }
    }
}
