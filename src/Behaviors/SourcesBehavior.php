<?php

namespace NerdsAndCompany\Schematic\Behaviors;

use Craft;
use TypeError;
use yii\base\Behavior;
use craft\base\Model;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Sources Behavior.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SourcesBehavior extends Behavior
{
    /**
     * Recursively find sources in definition attributes.
     *
     * @param string $fieldType
     * @param array  $attributes
     * @param string $indexFrom
     * @param string $indexTo
     *
     * @return array
     */
    public function findSources(string $fieldType, array $attributes, string $indexFrom, string $indexTo): array
    {
        foreach ($attributes as $key => $attribute) {
            if ($key === 'source') {
                $attributes[$key] = $this->getSource($fieldType, $attribute, $indexFrom, $indexTo);
            } elseif ($key === 'sources') {
                $attributes[$key] = $this->getSources($fieldType, $attribute, $indexFrom, $indexTo);
            } elseif (is_array($attribute)) {
                $attributes[$key] = $this->findSources($fieldType, $attribute, $indexFrom, $indexTo);
            }
        }

        return $attributes;
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
    public function getSources(string $fieldType, $sources, string $indexFrom, string $indexTo)
    {
        $mappedSources = $sources;
        if (is_array($sources)) {
            $mappedSources = [];
            $sources = array_filter($sources);
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
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSource(string $fieldType, string $source = null, string $indexFrom, string $indexTo)
    {
        if (false === strpos($source, ':')) {
            return $source;
        }

        /** @var Model $sourceObject */
        $sourceObject = null;

        list($sourceType, $sourceFrom) = explode(':', $source);
        switch ($sourceType) {
            case 'single':
            case 'section':
            case 'createEntries':
            case 'editPeerEntries':
            case 'deleteEntries':
            case 'deletePeerEntries':
            case 'deletePeerEntryDrafts':
            case 'editEntries':
            case 'editPeerEntryDrafts':
            case 'publishEntries':
            case 'publishPeerEntries':
            case 'publishPeerEntryDrafts':
                $service = Craft::$app->sections;
                $method = 'getSectionBy';
                break;
            case 'group':
            case 'editCategories':
                $service = 'Users' == $fieldType ? Craft::$app->userGroups : Craft::$app->categories;
                $method = 'getGroupBy';
                break;
            case 'folder':
            case 'createFoldersInVolume':
            case 'deleteFilesAndFoldersInVolume':
            case 'saveAssetInVolume':
            case 'viewVolume':
                $service = Craft::$app->volumes;
                $method = 'getVolumeBy';
                break;
            case 'taggroup':
                $service = Craft::$app->tags;
                $method = 'getTagGroupBy';
                break;
            case 'field':
                $service = Craft::$app->fields;
                $method = 'getFieldBy';
                break;
            case 'editGlobalSet':
                $service = Craft::$app->globals;
                $method = 'getSetBy';
                break;
            case 'utility':
                return $source;
        }

        if (isset($service) && isset($method) && isset($sourceFrom)) {
            $method = $method.ucfirst($indexFrom);
            try {
                $sourceObject = $service->$method($sourceFrom);
            } catch (TypeError $e) {
                Schematic::error('An error occured mapping source '.$source.' from '.$indexFrom.' to '.$indexTo);
                Schematic::error($e->getMessage());

                return null;
            }
        }

        if ($sourceObject) {
            return $sourceType.':'.$sourceObject->$indexTo;
        }

        Schematic::warning('No mapping found for source '.$source);

        return null;
    }
}
