<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;

/**
 * Schematic Element Index Settings Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ElementIndexSettings extends BaseComponent implements MappingInterface
{
    /**
     * Load sources behaviors
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          SourcesBehavior::className(),
        ];
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * @param array $data
     *
     * @return array
     */
    public function export()
    {
        $settingDefinitions = [];
        $elementTypes = Craft::$app->elements->getAllElementTypes();
        foreach ($elementTypes as $elementType) {
            $elementTypeName = str_replace('craft\\elements\\', '', $elementType);
            $settings = Craft::$app->elementIndexes->getSettings($elementType);
            if (is_array($settings)) {
                $mappedSettings = $this->getMappedSettings($settings, 'id', 'handle');
                $settingDefinitions[$elementTypeName] = $mappedSettings;
            }
        }

        return $settingDefinitions;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * @param array $settingDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import($force = false, array $settingDefinitions = null)
    {
        Craft::info('Importing Element Index Settings', 'schematic');

        foreach ($settingDefinitions as $elementType => $settings) {
            $mappedSettings = $this->getMappedSettings($settings, 'handle', 'id');
            if (!$this->getElementIndexesService()->saveSettings($elementType, $mappedSettings)) {
                $this->addError('Element Index Settings for {elementType} could not be installed', ['elementType' => $elementType]);
            }
        }

        return $this->getResultModel();
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
                    $row[1] = $this->getSource(false, $row[1], $fromIndex, $toIndex);
                }
                $mappedSettings['sourceOrder'][] = $row;
            }
        }

        if (isset($settings['sources'])) {
            foreach ($settings['sources'] as $source => $sourceSettings) {
                $mappedSource = $this->getSource(false, $source, $fromIndex, $toIndex);
                $mappedSettings['sources'][$mappedSource] = [
                  'tableAttributes' => $this->getSources('', $sourceSettings['tableAttributes'], $fromIndex, $toIndex),
                ];
            }
        }

        return $mappedSettings;
    }
}
