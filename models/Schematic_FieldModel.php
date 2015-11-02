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

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

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
            $definition['settings']['sources'] = $this->getSourceHandles($definition['settings']['sources']);
        }

        return $definition;
    }

    /**
     * Get source handles.
     *
     * @param string|array $sourcesWithIds
     *
     * @return string|array
     */
    private function getSourceHandles($sourcesWithIds)
    {
        if (!is_array($sourcesWithIds)) {
            return $sourcesWithIds;
        }
        $sourcesWithHandles = array();
        foreach ($sourcesWithIds as $sourceWithId) {
            $sourcesWithHandles[] = $this->getSourceHandle($sourceWithId);
        }

        return $sourcesWithHandles;
    }

    /**
     * @param string $source with id
     * @return string source with handle
     */
    private function getSourceHandle($source)
    {
        if (strpos($source, ':') > -1) {
            /** @var BaseElementModel $sourceObject */
            $sourceObject = null;
            list($sourceType, $sourceId) = explode(':', $source);

            switch ($sourceType) {
                case 'section':
                    $sourceObject = craft()->sections->getSectionById($sourceId);
                    break;
                case 'group':
                    $sourceObject = craft()->userGroups->getGroupById($sourceId);
                    break;
            }
            if ($sourceObject) {
                $source = $sourceType . ':' . $sourceObject->handle;
            }
        }
        return $source;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

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
            $settings['sources'] = $this->getSourceIds($settings['sources']);
            $field->settings = $settings;
        }
    }

    /**
     * Get source id's.
     *
     * @param string|array $sourcesWithHandle
     *
     * @return string|array
     */
    private function getSourceIds($sourcesWithHandle)
    {
        if (!is_array($sourcesWithHandle)) {
            return $sourcesWithHandle;
        }
        $sourcesWithIds = array();
        foreach ($sourcesWithHandle as $sourceWithHandle) {
            $sourcesWithIds[] = $this->getSourceId($sourceWithHandle);
        }
        return $sourcesWithIds;
    }

    /**
     * @param $source
     * @return string
     */
    private function getSourceId($source)
    {
        /** @var BaseElementModel $sourceObject */
        $sourceObject = null;
        if (strpos($source, ':') > -1) {
            list($sourceType, $sourceHandle) = explode(':', $source);

            switch ($sourceType) {
                case 'section':
                    $sourceObject = craft()->sections->getSectionByHandle($sourceHandle);
                    break;
                case 'group':
                    $sourceObject = craft()->userGroups->getGroupByHandle($sourceHandle);
                    break;
            }
        } elseif ($source !== 'singles') {
            //Backwards compatibility
            $sourceType = 'section';
            $sourceObject = craft()->sections->getSectionByHandle($source);
        }
        if ($sourceObject && isset($sourceType)) {
            $source = $sourceType . ':' . $sourceObject->id;
        }

        return $source;
    }
}
