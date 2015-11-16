<?php

namespace Craft;

/**
 * Schematic Categories Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_CategoriesService extends Schematic_AbstractService
{
    /**
     * Export category groups.
     *
     * @param CategoryGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups = array())
    {
        $groupDefinitions = array();

        foreach ($groups as $group) {
            $groupDefinitions[$group->handle] = $this->getGroupDefinition($group);
        }

        return $groupDefinitions;
    }

    /**
     * Get category group definition.
     *
     * @param CategoryGroupModel $group
     *
     * @return array
     */
    private function getGroupDefinition(CategoryGroupModel $group)
    {
        return array(
            'name' => $group->name,
            'hasUrls' => $group->hasUrls,
            'template' => $group->template,
            'maxLevels' => $group->maxLevels,
            'locales' => $this->getLocaleDefinitions($group->getLocales()),
            'fieldLayout' => craft()->schematic_fields->getFieldLayoutDefinition($group->getFieldLayout()),
        );
    }

    /**
     * Get locale definitions.
     *
     * @param CategoryGroupLocaleModel[] $locales
     *
     * @return array
     */
    private function getLocaleDefinitions(array $locales)
    {
        $localeDefinitions = array();

        foreach ($locales as $locale) {
            $localeDefinitions[$locale->locale] = $this->getLocaleDefinition($locale);
        }

        return $localeDefinitions;
    }

    /**
     * Get locale definition.
     *
     * @param CategoryGroupLocaleModel $locale
     *
     * @return array
     */
    private function getLocaleDefinition(CategoryGroupLocaleModel $locale)
    {
        return array(
            'urlFormat' => $locale->urlFormat,
            'nestedUrlFormat' => $locale->nestedUrlFormat,
        );
    }

    /**
     * Attempt to import category groups.
     *
     * @param array $categoryGroupDefinitions
     * @param bool  $force                If set to true category groups not included in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import(array $categoryGroupDefinitions, $force = false)
    {
        $groups = craft()->categories->getAllGroups('handle');

        foreach ($categoryGroupDefinitions as $groupHandle => $groupDefinition) {
            $group = array_key_exists($groupHandle, $groups)
                ? $groups[$groupHandle]
                : new CategoryGroupModel();

            unset($groups[$groupHandle]);

            $this->populateCategoryGroup($group, $groupDefinition, $groupHandle);

            if (!craft()->categories->saveGroup($group)) { // Save category group via craft
                $this->addErrors($group->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($groups as $categoryGroup) {
                craft()->categories->deleteGroupById($categoryGroup->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate category group.
     *
     * @param CategoryGroupModel $categoryGroup
     * @param array          $categoryGroupDefinition
     * @param string         $categoryGroupHandle
     */
    private function populateCategoryGroup(CategoryGroupModel $categoryGroup, array $categoryGroupDefinition, $categoryGroupHandle)
    {
        $categoryGroup->setAttributes(array(
            'handle' => $categoryGroupHandle,
            'name' => $categoryGroupDefinition['name'],
            'hasUrls' => $categoryGroupDefinition['hasUrls'],
            'template' => $categoryGroupDefinition['template'],
            'maxLevels' => $categoryGroupDefinition['maxLevels'],
        ));

        $fieldLayout = craft()->schematic_fields->getFieldLayout($categoryGroupDefinition['fieldLayout']);
        $categoryGroup->setFieldLayout($fieldLayout);

        $this->populateGroupLocales($categoryGroup, $categoryGroupDefinition['locales']);
    }

    /**
     * Populate category group locales.
     *
     * @param CategoryGroupModel $categoryGroup
     * @param $localeDefinitions
     */
    private function populateGroupLocales(CategoryGroupModel $categoryGroup, $localeDefinitions)
    {
        $locales = $categoryGroup->getLocales();

        foreach ($localeDefinitions as $localeId => $localeDef) {
            $locale = array_key_exists($localeId, $locales) ? $locales[$localeId] : new CategoryGroupLocaleModel();

            $locale->setAttributes(array(
                'locale' => $localeId,
                'urlFormat' => $localeDef['urlFormat'],
                'nestedUrlFormat' => strlen($localeDef['nestedUrlFormat']) > 0 ? $localeDef['nestedUrlFormat'] : '{parent.uri}/{slug}',
            ));

            // Todo: Is this a hack? I don't see another way.
            // Todo: Might need a sorting order as well? It's NULL at the moment.
            craft()->db->createCommand()->insertOrUpdate('locales', array(
                'locale' => $locale->locale,
            ), array());

            $locales[$localeId] = $locale;
        }

        $categoryGroup->setLocales($locales);
    }
}
