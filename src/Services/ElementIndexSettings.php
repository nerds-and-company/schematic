<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;

/**
 * Schematic Element Index Settings Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ElementIndexSettings extends Base
{
    /**
     * @return ElementsService
     */
    protected function getElementsService()
    {
        return Craft::app()->elements;
    }

    /**
     * @return ElementIndexesService
     */
    protected function getElementIndexesService()
    {
        return Craft::app()->elementIndexes;
    }

    /**
     * @param array $settingDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $settingDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Element Index Settings'));

        foreach ($settingDefinitions as $elementType => $settings) {
            $mappedSettings = $this->getMappedSettings($settings, 'handle', 'id');
            if (!$this->getElementIndexesService()->saveSettings($elementType, $mappedSettings)) {
                $this->addError(Craft::t('Element Index Settings for {elementType} could not be installed', ['elementType' => $elementType]));
            }
        }

        return $this->getResultModel();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Element Index Settings'));

        $settingDefinitions = [];

        // Get all element types
        $elementTypes = $this->getElementsService()->getAllElementTypes();

        // Loop through element types
        foreach ($elementTypes as $elementType) {
            // Get element type name
            $elementTypeName = preg_replace('/^Craft\\\(.*?)ElementType$/', '$1', get_class($elementType));

            // Get existing settings for element type
            $settings = $this->getElementIndexesService()->getSettings($elementTypeName);

            // If there are settings, export
            if (is_array($settings)) {
                // Group by element type and add to definitions
                $mappedSettings = $this->getMappedSettings($settings, 'id', 'handle');
                $settingDefinitions[$elementTypeName] = $mappedSettings;
            }
        }

        return $settingDefinitions;
    }

    /**
     * Get mapped element index settings, converting source ids to handles or back again.
     *
     * @param array  $settings
     * @param string $fromIndex
     * @param string $toIndex
     *
     * @return array
     */
    private function getMappedSettings(array $settings, $fromIndex, $toIndex)
    {
        $mappedSettings = ['sourceOrder' => [], 'sources' => []];

        if (isset($settings['sourceOrder'])) {
            foreach ($settings['sourceOrder'] as $row) {
                if ($row[0] == 'key') {
                    $row[1] = Craft::app()->schematic_sources->getSource(false, $row[1], $fromIndex, $toIndex);
                }
                $mappedSettings['sourceOrder'][] = $row;
            }
        }

        if (isset($settings['sources'])) {
            foreach ($settings['sources'] as $source => $sourceSettings) {
                $mappedSource = Craft::app()->schematic_sources->getSource(false, $source, $fromIndex, $toIndex);
                $tableAttributesSettings = [];
                foreach ($sourceSettings['tableAttributes'] as $index => $columnSource) {
                    $mappedColumnSource = Craft::app()->schematic_sources->getSource(false, $columnSource, $fromIndex, $toIndex);
                    $tableAttributesSettings[$index] = $mappedColumnSource;
                }
                $mappedSettings['sources'][$mappedSource] = ['tableAttributes' => $tableAttributesSettings];
            }
        }

        return $mappedSettings;
    }
}
