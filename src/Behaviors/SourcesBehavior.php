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
     * Get sources based on the indexFrom attribute and return them with the indexTo attribute.
     *
     * @param string       $fieldType
     * @param string|array $sources
     * @param string       $indexFrom
     * @param string       $indexTo
     *
     * @return array|string
     */
    public function getSources($fieldType, $sources, $indexFrom, $indexTo)
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSource($fieldType, $source, $indexFrom, $indexTo)
    {
        if (false === strpos($source, ':')) {
            return $source;
        }

        /** @var Model $sourceObject */
        $sourceObject = null;

        list($sourceType, $sourceFrom) = explode(':', $source);
        switch ($sourceType) {
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
            }
        }

        if ($sourceObject && isset($sourceType)) {
            return $sourceType.':'.$sourceObject->$indexTo;
        }

        Schematic::warning('No mapping found for source '.$source);

        return $source;
    }
}
