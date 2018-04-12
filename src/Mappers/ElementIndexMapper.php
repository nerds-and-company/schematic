<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use yii\base\Component as BaseComponent;

/**
 * Schematic Element Index Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ElementIndexMapper extends BaseComponent implements MapperInterface
{
    /**
     * Load sources behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          SourcesBehavior::className(),
        ];
    }

    /**
     * @return array
     */
    public function export(array $elementTypes): array
    {
        $settingDefinitions = [];
        foreach ($elementTypes as $elementType) {
            $settings = Craft::$app->elementIndexes->getSettings($elementType);
            if (is_array($settings)) {
                $elementTypeName = str_replace('craft\\elements\\', '', $elementType);
                $settingDefinitions[$elementTypeName] = $this->getMappedSettings($settings, 'id', 'handle');
            }
        }

        return $settingDefinitions;
    }

    /**
     * @param array $settingDefinitions
     *
     * @return Result
     */
    public function import(array $settingDefinitions, array $elementTypes): array
    {
        Schematic::warning('Element index import is not yet implemented');

        foreach ($settingDefinitions as $elementType => $settings) {
            $mappedSettings = $this->getMappedSettings($settings, 'handle', 'id');
            // Import the settings
        }

        return $elementTypes;
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
                if ('key' == $row[0]) {
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
