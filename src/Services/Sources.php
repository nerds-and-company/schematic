<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseApplicationComponent as BaseApplication;

/**
 * Schematic Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Sources extends BaseApplication
{
    /**
     * @var array()
     */
    private $hookedSources = [];

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
   public function getMappedSources($fieldType, $sources, $indexFrom, $indexTo)
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
    */
   public function getSource($fieldType, $source, $indexFrom, $indexTo)
   {
       if (strpos($source, ':') === false) {
           return $source;
       }

       /** @var BaseElementModel $sourceObject */
       $sourceObject = null;

       list($sourceType, $sourceFrom) = explode(':', $source);
       switch ($sourceType) {
          case 'section':
          case 'createEntries':
          case 'deleteEntries':
          case 'deletePeerEntries':
          case 'deletePeerEntryDrafts':
          case 'editEntries':
          case 'editPeerEntries':
          case 'editPeerEntryDrafts':
          case 'publishEntries':
          case 'publishPeerEntries':
          case 'publishPeerEntryDrafts':
              $service = Craft::app()->sections;
              $method = 'getSectionBy';
              break;
          case 'group':
          case 'editCategories':
              $service = $fieldType == 'Users' ? Craft::app()->userGroups : Craft::app()->categories;
              $method = 'getGroupBy';
              break;
          case 'folder':
          case 'createSubfoldersInAssetSource':
          case 'removeFromAssetSource':
          case 'uploadToAssetSource':
          case 'viewAssetSource':
              $service = Craft::app()->schematic_assetSources;
              $method = 'getSourceBy';
              break;
          case 'taggroup':
              $service = Craft::app()->tags;
              $method = 'getTagGroupBy';
              break;
          case 'field':
              $service = Craft::app()->fields;
              $method = 'getFieldBy';
              break;
          case 'editGlobalSet':
              $service = Craft::app()->globals;
              $method = 'getSetBy';
              break;
          case 'editLocale':
              return $source;
          case 'assignUserGroup':
              $service = Craft::app()->userGroups;
              $method = 'getGroupBy';
              break;
       }

       if (isset($service) && isset($method) && isset($sourceFrom)) {
           $method = $method.ucfirst($indexFrom);
           $sourceObject = $service->$method($sourceFrom);
       }

       if ($sourceObject && isset($sourceType)) {
           return $sourceType.':'.$sourceObject->$indexTo;
       }

       return $this->getHookedSource($source, $indexFrom);
   }

    /**
     * See if the source can be found in the hooked sources.
     *
     * @param string $source
     * @param string $indexFrom
     *
     * @return string
     */
    private function getHookedSource($source, $indexFrom)
    {
        $this->loadHookedSources($indexFrom);
        foreach ($this->hookedSources[$indexFrom] as $hookedSources) {
            if (array_key_exists($source, $hookedSources)) {
                return $hookedSources[$source];
            }
        }

        return '';
    }

   /**
    * Load the hooked sources.
    */
   private function loadHookedSources($indexFrom)
   {
       if (!isset($this->hookedSources[$indexFrom])) {
           $this->hookedSources[$indexFrom] = Craft::app()->plugins->call('registerSchematicSources', [$indexFrom]);
       }
   }
}
