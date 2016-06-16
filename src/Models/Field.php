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
 * @copyright Copyright (c) 2015-2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
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
            $definition['settings']['sources'] = $this->getMappedSources($field->type, $definition['settings']['sources'], 'id', 'handle');
        }

        if (isset($definition['settings']['source'])) {
            $definition['settings']['source'] = $this->getSource($field->type, $definition['settings']['source'], 'id', 'handle');
        }

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

        if (isset($fieldDefinition['settings']['sources'])) {
            $settings = $fieldDefinition['settings'];
            $settings['sources'] = $this->getMappedSources($field->type, $settings['sources'], 'handle', 'id');
            $field->settings = $settings;
        }

        if (isset($fieldDefinition['settings']['source'])) {
            $settings = $fieldDefinition['settings'];
            $settings['source'] = $this->getSource($field->type, $settings['source'], 'handle', 'id');
            $field->settings = $settings;
        }
    }

    /**
     * Get sources based on the indexFrom attribute and return them with the indexTo attribute.
     *
     * @param string       $fieldType
     * @param string|array $sources
     * @param string       $indexFrom
     * @param string       $indexTo
     *
     * @return array|string
     */
    private function getMappedSources($fieldType, $sources, $indexFrom, $indexTo)
    {
        $mappedSources = $sources;
        if (is_array($sources)) {
            $mappedSources = [];
            foreach ($sources as $source) {
                $mappedSources[] = $this->getSource($fieldType, $source, $indexFrom, $indexTo);
            }
        }

        return $mappedSources;
    }

    /**
     * Gets a source by the attribute indexFrom, and returns it with attribute $indexTo.
     *
     * @TODO Break up and simplify this method
     *
     * @param string $fieldType
     * @param string $source
     * @param string $indexFrom
     * @param string $indexTo
     *
     * @return string
     */
    private function getSource($fieldType, $source, $indexFrom, $indexTo)
    {
        if ($source == 'singles' || $source == '*') {
            return $source;
        }

        /** @var BaseElementModel $sourceObject */
        $sourceObject = null;

        if (strpos($source, ':') > -1) {
            list($sourceType, $sourceFrom) = explode(':', $source);
            switch ($sourceType) {
                case 'section':
                    $service = Craft::app()->sections;
                    $method = 'getSectionBy';
                    break;
                case 'group':
                    $service = $fieldType == 'Users' ? Craft::app()->userGroups : Craft::app()->categories;
                    $method = 'getGroupBy';
                    break;
                case 'folder':
                    $service = Craft::app()->assetSources;
                    $method = 'getSourceTypeBy';
                    break;
                case 'taggroup':
                    $service = Craft::app()->tags;
                    $method = 'getTagGroupBy';
                    break;
            }
        } elseif ($source !== 'singles') {
            //Backwards compatibility
            $sourceType = 'section';
            $sourceFrom = $source;
            $service = Craft::app()->sections;
            $method = 'getSectionBy';
        }

        if (isset($service) && isset($method) && isset($sourceFrom)) {
            $method = $method.$indexFrom;
            $sourceObject = $service->$method($sourceFrom);
        }

        if ($sourceObject && isset($sourceType)) {
            return $sourceType.':'.$sourceObject->$indexTo;
        }
        return '';
    }
}
