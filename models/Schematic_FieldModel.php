<?php

namespace Craft;

/**
 * Class Schematic_FieldModel
 */
class Schematic_FieldModel
{
    /**
     * @return Schematic_FieldFactoryModel
     */
    public function getFieldFactory()
    {
        return craft()->schematic_fields->getFieldFactory();
    }

    /**
     * @return SectionsService
     */
    private function getSectionsService()
    {
        return craft()->sections;
    }

    /**
     * @return UserGroupsService
     */
    private function getUserGroupsService()
    {
        return craft()->userGroups;
    }

    /**
     * @param FieldModel $field
     * @param $includeContext
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext)
    {
        $definition = array(
            'name' => $field->name,
            'required' => $field->required,
            'instructions' => $field->instructions,
            'translatable' => $field->translatable,
            'type' => $field->type,
            'settings' => $field->settings,
        );

        if ($includeContext) {
            $definition['context'] = $field->context;
        }

        if (isset($definition['settings']['sources'])) {
            $definition['settings']['sources'] = $this->getMappedSources($definition['settings']['sources'], 'id', 'handle');
        }

        return $definition;
    }

    /**
     * @param array $fieldDefinition
     * @param FieldModel $field
     * @param string $fieldHandle
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

        if (isset($definition['settings']['sources'])) {
            $settings = $fieldDefinition['settings'];
            $settings['sources'] = $this->getMappedSources($settings['sources'], 'handle', 'id');
            $field->settings = $settings;
        }
    }

    /**
     * Get sources based on the indexFrom attribute and return them with the indexTo attribute
     *
     * @param string|array $sources
     *
     * @return string|array
     */
    private function getMappedSources($sources, $indexFrom, $indexTo)
    {
        if (!is_array($sources)) {
            return $sources;
        }

        $mappedSources = array();
        foreach ($sources as $source) {
            $mappedSources[] = $this->getSource($source, $indexFrom, $indexTo);
        }

        return $mappedSources;
    }

    /**
     * Gets a source by the attribute indexFrom, and returns it with attribute $indexTo
     * @param string $source
     * @param string $indexFrom
     * @param string $indexTo
     * @return string
     */
    private function getSource($source, $indexFrom, $indexTo)
    {
        /** @var BaseElementModel $sourceObject */
        $sourceObject = null;

        if (strpos($source, ':') > -1) {
            list($sourceType, $sourceFrom) = explode(':', $source);
            switch ($sourceType) {
                case 'section':
                    $service = $this->getSectionsService();
                    $method = 'getSectionBy';
                    break;
                case 'group':
                    $service = $this->getUserGroupsService();
                    $method = 'getGroupBy';
                    break;
            }
        } elseif ($source !== 'singles') {
            //Backwards compatibility
            $sourceType = 'section';
            $service = $this->getSectionsService();
            $method = 'getSectionBy';
        }

        if(isset($service) && isset($method)){
            $method = $method . $indexFrom;
            $sourceObject = $service->$method($source->$indexFrom);
        }

        if ($sourceObject && isset($sourceType)) {
            $source = $sourceType . ':' . $sourceObject->$indexTo;
        }

        return $source;
    }
}
