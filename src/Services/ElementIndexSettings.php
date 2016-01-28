<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;

/**
 * Schematic Element Index Settings Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
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
            if (!$this->getElementIndexesService()->saveSettings($elementType, $settings)) {
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
                $settingDefinitions[$elementTypeName] = $settings;
            }
        }

        return $settingDefinitions;
    }
}
