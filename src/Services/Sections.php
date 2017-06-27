<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\SectionRecord;
use Craft\SectionModel;
use Craft\SectionLocaleModel;
use Craft\EntryTypeModel;

/**
 * Schematic Result Model.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Sections extends Base
{
    /**
     * Export sections.
     *
     * @param SectionModel[] $sections
     * @param array|null     $allowedEntryTypeIds
     *
     * @return array
     */
    public function export(array $sections = [], array $allowedEntryTypeIds = null)
    {
        Craft::log(Craft::t('Exporting Sections'));

        $sectionDefinitions = [];

        foreach ($sections as $section) {
            $sectionDefinitions[$section->handle] = $this->getSectionDefinition($section, $allowedEntryTypeIds);
        }

        return $sectionDefinitions;
    }

    /**
     * Get section definition.
     *
     * @param SectionModel $section
     * @param $allowedEntryTypeIds
     *
     * @return array
     */
    private function getSectionDefinition(SectionModel $section, $allowedEntryTypeIds)
    {
        return [
            'name' => $section->name,
            'type' => $section->type,
            'hasUrls' => $section->hasUrls,
            'template' => $section->template,
            'maxLevels' => $section->maxLevels,
            'enableVersioning' => $section->enableVersioning,
            'locales' => $this->getLocaleDefinitions($section->getLocales()),
            'entryTypes' => $this->getEntryTypeDefinitions($section->getEntryTypes(), $allowedEntryTypeIds),
        ];
    }

    /**
     * Get locale definitions.
     *
     * @param SectionLocaleModel[] $locales
     *
     * @return array
     */
    private function getLocaleDefinitions(array $locales)
    {
        $localeDefinitions = [];

        foreach ($locales as $locale) {
            $localeDefinitions[$locale->locale] = $this->getLocaleDefinition($locale);
        }

        return $localeDefinitions;
    }

    /**
     * Get locale definition.
     *
     * @param SectionLocaleModel $locale
     *
     * @return array
     */
    private function getLocaleDefinition(SectionLocaleModel $locale)
    {
        return [
            'enabledByDefault' => $locale->enabledByDefault,
            'urlFormat' => $locale->urlFormat,
            'nestedUrlFormat' => $locale->nestedUrlFormat,
        ];
    }

    /**
     * Get entry type definitions.
     *
     * @param array $entryTypes
     * @param $allowedEntryTypeIds
     *
     * @return array
     */
    private function getEntryTypeDefinitions(array $entryTypes, $allowedEntryTypeIds)
    {
        $entryTypeDefinitions = [];

        foreach ($entryTypes as $entryType) {
            if ($allowedEntryTypeIds === null || in_array($entryType->id, $allowedEntryTypeIds)) {
                $entryTypeDefinitions[$entryType->handle] = $this->getEntryTypeDefinition($entryType);
            }
        }

        return $entryTypeDefinitions;
    }

    /**
     * Get entry type definition.
     *
     * @param EntryTypeModel $entryType
     *
     * @return array
     */
    private function getEntryTypeDefinition(EntryTypeModel $entryType)
    {
        return [
            'name' => $entryType->name,
            'hasTitleField' => $entryType->hasTitleField,
            'titleLabel' => $entryType->titleLabel,
            'titleFormat' => $entryType->titleFormat,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($entryType->getFieldLayout()),
        ];
    }

    /**
     * Attempt to import sections.
     *
     * @param array $sectionDefinitions
     * @param bool  $force              If set to true sections not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $sectionDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Sections'));

        $sections = Craft::app()->sections->getAllSections('handle');

        foreach ($sectionDefinitions as $sectionHandle => $sectionDefinition) {
            $section = array_key_exists($sectionHandle, $sections)
                ? $sections[$sectionHandle]
                : new SectionModel();

            unset($sections[$sectionHandle]);

            if ($sectionDefinition === $this->getSectionDefinition($section, null)) {
                Craft::log(Craft::t('Skipping `{name}`, no changes detected', ['name' => $section->name]));
                continue;
            }

            if (!array_key_exists('locales', $sectionDefinition)) {
                $this->addError('`sections[handle].locales` must be defined');

                continue;
            }

            if (!array_key_exists('entryTypes', $sectionDefinition)) {
                $this->addError('errors', '`sections[handle].entryTypes` must exist be defined');

                continue;
            }

            Craft::log(Craft::t('Importing section `{name}`', ['name' => $sectionDefinition['name']]));

            $this->populateSection($section, $sectionDefinition, $sectionHandle);
            $this->resetCraftFieldsSectionModelCache($section);

            // Create initial section record
            if (!$this->preSaveSection($section)) {
                $this->addErrors($section->getAllErrors());

                continue;
            }

            $this->importEntryTypes($section, $sectionDefinition['entryTypes'], $force);

            // Save section via craft after entrytypes have been created
            if (!Craft::app()->sections->saveSection($section)) {
                $this->addErrors($section->getAllErrors());
            }
        }

        if ($force) {
            foreach ($sections as $section) {
                Craft::app()->sections->deleteSectionById($section->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * @param SectionModel $section
     * @param array        $entryTypeDefinitions
     * @param bool         $force
     */
    private function importEntryTypes(SectionModel $section, array $entryTypeDefinitions, $force)
    {
        $entryTypes = Craft::app()->sections->getEntryTypesBySectionId($section->id, 'handle');

        foreach ($entryTypeDefinitions as $entryTypeHandle => $entryTypeDefinition) {
            $entryType = array_key_exists($entryTypeHandle, $entryTypes)
                ? $entryTypes[$entryTypeHandle]
                : new EntryTypeModel();

            unset($entryTypes[$entryTypeHandle]);

            $this->populateEntryType($entryType, $entryTypeDefinition, $entryTypeHandle, $section->id);

            if (!Craft::app()->sections->saveEntryType($entryType)) {
                $this->addError($entryType->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($entryTypes as $entryType) {
                Craft::app()->sections->deleteEntryTypeById($entryType->id);
            }
        }
    }

    /**
     * Save the section manually if it is new to prevent craft from creating the default entry type
     * In case of a single we do want the default entry type and do a normal save
     * Todo: This method is a bit hackish, find a better way.
     *
     * @param SectionModel $section
     *
     * @return mixed
     */
    private function preSaveSection(SectionModel $section)
    {
        if ($section->type != 'single' && !$section->id) {
            $sectionRecord = new SectionRecord();

            // Shared attributes
            $sectionRecord->name = $section->name;
            $sectionRecord->handle = $section->handle;
            $sectionRecord->type = $section->type;
            $sectionRecord->enableVersioning = $section->enableVersioning;

            if (!$sectionRecord->save()) {
                $section->addErrors(['errors' => $sectionRecord->getErrors()]);

                return false;
            }
            $section->id = $sectionRecord->id;

            return true;
        }

        return Craft::app()->sections->saveSection($section);
    }

    /**
     * Populate section.
     *
     * @param SectionModel $section
     * @param array        $sectionDefinition
     * @param string       $sectionHandle
     */
    private function populateSection(SectionModel $section, array $sectionDefinition, $sectionHandle)
    {
        $section->setAttributes([
            'handle' => $sectionHandle,
            'name' => $sectionDefinition['name'],
            'type' => $sectionDefinition['type'],
            'hasUrls' => $sectionDefinition['hasUrls'],
            'template' => $sectionDefinition['template'],
            'maxLevels' => $sectionDefinition['maxLevels'],
            'enableVersioning' => $sectionDefinition['enableVersioning'],
        ]);

        $this->populateSectionLocales($section, $sectionDefinition['locales']);
    }

    /**
     * Populate section locales.
     *
     * @param SectionModel $section
     * @param $localeDefinitions
     */
    private function populateSectionLocales(SectionModel $section, $localeDefinitions)
    {
        $locales = $section->getLocales();

        foreach ($localeDefinitions as $localeId => $localeDef) {
            $locale = array_key_exists($localeId, $locales) ? $locales[$localeId] : new SectionLocaleModel();

            $locale->setAttributes([
                'locale' => $localeId,
                'enabledByDefault' => $localeDef['enabledByDefault'],
                'urlFormat' => $localeDef['urlFormat'],
                'nestedUrlFormat' => $localeDef['nestedUrlFormat'],
            ]);

            // Todo: Is this a hack? I don't see another way.
            // Todo: Might need a sorting order as well? It's NULL at the moment.
            Craft::app()->db->createCommand()->insertOrUpdate('locales', [
                'locale' => $locale->locale,
            ], []);

            $locales[$localeId] = $locale;
        }

        $section->setLocales($locales);
    }

    /**
     * Populate entry type.
     *
     * @param EntryTypeModel $entryType
     * @param array          $entryTypeDefinition
     * @param string         $entryTypeHandle
     * @param int            $sectionId
     */
    private function populateEntryType(EntryTypeModel $entryType, array $entryTypeDefinition, $entryTypeHandle, $sectionId)
    {
        $entryType->setAttributes([
            'handle' => $entryTypeHandle,
            'sectionId' => $sectionId,
            'name' => $entryTypeDefinition['name'],
            'hasTitleField' => $entryTypeDefinition['hasTitleField'],
            'titleLabel' => $entryTypeDefinition['titleLabel'],
            'titleFormat' => $entryTypeDefinition['titleFormat'],
        ]);

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($entryTypeDefinition['fieldLayout']);
        $entryType->setFieldLayout($fieldLayout);
    }

    /**
     * Reset craft section model cache using reflection.
     *
     * @param SectionModel $section
     */
    private function resetCraftFieldsSectionModelCache(SectionModel $section)
    {
        $obj = $section;
        $refObject = new \ReflectionObject($obj);
        $refProperty = $refObject->getProperty('_entryTypes');
        $refProperty->setAccessible(true);
        $refProperty->setValue($obj, null);
    }
}
