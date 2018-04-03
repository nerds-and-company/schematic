<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\CategoryGroup;

/**
 * Schematic Category Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroups extends Base
{

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all category groups
     *
     * @return CategoryGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->categories->getAllGroups();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Attempt to import category groups.
     *
     * @param array $categoryGroupDefinitions
     * @param bool  $force                    If set to true category groups not included in the import will be deleted
     *
     * @return Result
     */
    public function import($force = false, array $categoryGroupDefinitions = null)
    {
        Craft::info('Importing Category Groups', 'schematic');

        $this->resetCraftCategoriesServiceCache();
        $categoryGroups = Craft::$app->categories->getAllGroups('handle');

        foreach ($categoryGroupDefinitions as $categoryGroupHandle => $categoryGroupDefinition) {
            $categoryGroup = array_key_exists($categoryGroupHandle, $categoryGroups)
                ? $categoryGroups[$categoryGroupHandle]
                : new CategoryGroupModel();

            unset($categoryGroups[$categoryGroupHandle]);

            $this->populateCategoryGroup($categoryGroup, $categoryGroupDefinition, $categoryGroupHandle);

            if (!Craft::$app->categories->saveGroup($categoryGroup)) { // Save categorygroup via craft
                $this->addErrors($categoryGroup->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($categoryGroups as $categoryGroup) {
                Craft::$app->categories->deleteGroupById($categoryGroup->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate categorygroup.
     *
     * @param CategoryGroupModel $categoryGroup
     * @param array              $categoryGroupDefinition
     * @param string             $categoryGroupHandle
     */
    private function populateCategoryGroup(CategoryGroupModel $categoryGroup, array $categoryGroupDefinition, $categoryGroupHandle)
    {
        $categoryGroup->setAttributes([
            'handle' => $categoryGroupHandle,
            'name' => $categoryGroupDefinition['name'],
            'hasUrls' => $categoryGroupDefinition['hasUrls'],
            'template' => $categoryGroupDefinition['template'],
            'maxLevels' => $categoryGroupDefinition['maxLevels'],
        ]);

        $this->populateCategoryGroupLocales($categoryGroup, $categoryGroupDefinition['locales']);

        $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($categoryGroupDefinition['fieldLayout']);
        $categoryGroup->setFieldLayout($fieldLayout);
    }

    /**
     * Populate section locales.
     *
     * @param CategoryGroupModel $categoryGroup
     * @param $localeDefinitions
     */
    private function populateCategoryGroupLocales(CategoryGroupModel $categoryGroup, $localeDefinitions)
    {
        $locales = $categoryGroup->getLocales();

        foreach ($localeDefinitions as $localeId => $localeDef) {
            $locale = array_key_exists($localeId, $locales) ? $locales[$localeId] : new CategoryGroupLocaleModel();

            $locale->setAttributes([
                'locale' => $localeId,
                'urlFormat' => $localeDef['urlFormat'],
                'nestedUrlFormat' => $localeDef['nestedUrlFormat'],
            ]);

            // Todo: Is this a hack? I don't see another way.
            // Todo: Might need a sorting order as well? It's NULL at the moment.
            Craft::$app->db->createCommand()->insertOrUpdate('locales', [
                'locale' => $locale->locale,
            ], []);

            $locales[$localeId] = $locale;
        }

        $categoryGroup->setLocales($locales);
    }

    /**
     * Reset craft fields service cache using reflection.
     */
    private function resetCraftCategoriesServiceCache()
    {
        $obj = Craft::$app->categories;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllCategoryGroups')) {
            $refProperty = $refObject->getProperty('_fetchedAllCategoryGroups');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_categoryGroupsById')) {
            $refProperty = $refObject->getProperty('_categoryGroupsById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
    }
}
