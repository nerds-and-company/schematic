<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\Section;
use craft\models\EntryType;

/**
 * Schematic Sections.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Sections extends Base
{
    /**
     * Get all section records
     *
     * @return Section[]
     */
    protected function getRecords()
    {
        return Craft::$app->sections->getAllSections();
    }

    /**
     * Get section definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $attributes = parent::getRecordDefinition($record);
        if ($record instanceof Section) {
            $attributes['entryTypes'] = $this->export($record->getEntryTypes());
        }
        if ($record instanceof EntryType) {
            unset($attributes['sectionId']);
        }

        return $attributes;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

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
        Craft::info(Craft::t('Importing Sections', 'schematic'));

        $sections = Craft::$app->sections->getAllSections('handle');

        foreach ($sectionDefinitions as $sectionHandle => $sectionDefinition) {
            $section = array_key_exists($sectionHandle, $sections)
                ? $sections[$sectionHandle]
                : new Section();

            unset($sections[$sectionHandle]);

            if ($sectionDefinition === $this->getSectionDefinition($section, null)) {
                Craft::info(Craft::t('Skipping `{name}`, no changes detected', ['name' => $section->name], 'schematic'));
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

            Craft::info(Craft::t('Importing section `{name}`', ['name' => $sectionDefinition['name']], 'schematic'));

            $this->populateSection($section, $sectionDefinition, $sectionHandle);

            // Create initial section record
            if (!$this->preSaveSection($section)) {
                $this->addErrors($section->getAllErrors());

                continue;
            }

            $this->importEntryTypes($section, $sectionDefinition['entryTypes'], $force);

            // Save section via craft after entrytypes have been created
            if (!Craft::$app->sections->saveSection($section)) {
                $this->addErrors($section->getAllErrors());
            }
        }

        if ($force) {
            foreach ($sections as $section) {
                Craft::$app->sections->deleteSectionById($section->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * @param Section $section
     * @param array   $entryTypeDefinitions
     * @param bool    $force
     */
    private function importEntryTypes(Section $section, array $entryTypeDefinitions, $force)
    {
        $entryTypes = Craft::$app->sections->getEntryTypesBySectionId($section->id, 'handle');

        foreach ($entryTypeDefinitions as $entryTypeHandle => $entryTypeDefinition) {
            $entryType = array_key_exists($entryTypeHandle, $entryTypes)
                ? $entryTypes[$entryTypeHandle]
                : new EntryTypeModel();

            unset($entryTypes[$entryTypeHandle]);

            $this->populateEntryType($entryType, $entryTypeDefinition, $entryTypeHandle, $section->id);

            if (!Craft::$app->sections->saveEntryType($entryType)) {
                $this->addError($entryType->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($entryTypes as $entryType) {
                Craft::$app->sections->deleteEntryTypeById($entryType->id);
            }
        }
    }

    /**
     * Save the section manually if it is new to prevent craft from creating the default entry type
     * In case of a single we do want the default entry type and do a normal save
     * Todo: This method is a bit hackish, find a better way.
     *
     * @param Section $section
     *
     * @return mixed
     */
    private function preSaveSection(Section $section)
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

        return Craft::$app->sections->saveSection($section);
    }

    /**
     * Populate section.
     *
     * @param Section $section
     * @param array   $sectionDefinition
     * @param string  $sectionHandle
     */
    private function populateSection(Section $section, array $sectionDefinition, $sectionHandle)
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
     * @param Section $section
     * @param $localeDefinitions
     */
    private function populateSectionLocales(Section $section, $localeDefinitions)
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
            Craft::$app->db->createCommand()->insertOrUpdate('locales', [
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

        $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($entryTypeDefinition['fieldLayout']);
        $entryType->setFieldLayout($fieldLayout);
    }
}
