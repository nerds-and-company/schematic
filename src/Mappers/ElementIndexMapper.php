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
 *
 * @method getSources(string $fieldType, $sources, string $indexFrom, string $indexTo)
 * @method getSource(string $fieldType, string $source, string $indexFrom, string $indexTo)
 */
class ElementIndexMapper extends BaseComponent implements MapperInterface
{
    /**
     * Load sources behaviors.
     *
     * @return array
     */
    public function behaviors(): array
    {
        return [
          SourcesBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $elementTypes): array
    {
        $settingDefinitions = [];
        foreach ($elementTypes as $elementType) {
            $settings = Craft::$app->elementIndexes->getSettings($elementType);
            if (is_array($settings)) {
                $settingDefinitions[$elementType] = $this->getMappedSettings($settings, 'id', 'handle');
            }
        }

        return $settingDefinitions;
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $settingDefinitions, array $elementTypes): array
    {
        foreach ($settingDefinitions as $elementType => $settings) {
            // Backwards compatibility
            if (class_exists('craft\\elements\\'.$elementType)) {
                $elementType = 'craft\\elements\\'.$elementType;
            }
            $mappedSettings = $this->getMappedSettings($settings, 'handle', 'id');
            if (!Craft::$app->elementIndexes->saveSettings($elementType, $mappedSettings)) {
                Schematic::error(' - Settings for '.$elementType.' could not be saved');
            }
        }

        return [];
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
                    $row[1] = $this->getSource('', $row[1], $fromIndex, $toIndex);
                }
                $mappedSettings['sourceOrder'][] = $row;
            }
        }

        if (isset($settings['sources'])) {
            foreach ($settings['sources'] as $source => $sourceSettings) {
                $mappedSource = $this->getSource('', $source, $fromIndex, $toIndex);
                $mappedSettings['sources'][$mappedSource] = [
                  'tableAttributes' => $this->getSources('', $sourceSettings['tableAttributes'], $fromIndex, $toIndex),
                ];
            }
        }

        return $mappedSettings;
    }
}
