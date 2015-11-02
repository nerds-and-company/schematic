<?php

namespace Craft;

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
}
