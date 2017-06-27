<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\TagGroupModel;

/**
 * Schematic TagGroups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TagGroups extends Base
{
    /**
     * Export taggroups.
     *
     * @param TagGroupModel[] $tagGroups
     *
     * @return array
     */
    public function export(array $tagGroups = [])
    {
        Craft::log(Craft::t('Exporting TagGroups'));

        $tagGroupDefinitions = [];

        foreach ($tagGroups as $tagGroup) {
            $tagGroupDefinitions[$tagGroup->handle] = $this->getTagGroupDefinition($tagGroup);
        }

        return $tagGroupDefinitions;
    }

    /**
     * Get tagGroup definition.
     *
     * @param TagGroupModel $tagGroup
     *
     * @return array
     */
    private function getTagGroupDefinition(TagGroupModel $tagGroup)
    {
        return [
            'name' => $tagGroup->name,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($tagGroup->getFieldLayout()),
        ];
    }

    /**
     * Attempt to import tagGroups.
     *
     * @param array $tagGroupDefinitions
     * @param bool  $force               If set to true tagGroups not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $tagGroupDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing TagGroups'));

        $tagGroups = Craft::app()->tags->getAllTagGroups('handle');

        foreach ($tagGroupDefinitions as $tagGroupHandle => $tagGroupDefinition) {
            $tagGroup = array_key_exists($tagGroupHandle, $tagGroups)
                ? $tagGroups[$tagGroupHandle]
                : new TagGroupModel();

            unset($tagGroups[$tagGroupHandle]);

            $this->populateTagGroup($tagGroup, $tagGroupDefinition, $tagGroupHandle);

            if (!Craft::app()->tags->saveTagGroup($tagGroup)) { // Save taggroup via craft
                $this->addErrors($tagGroup->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($tagGroups as $tagGroup) {
                Craft::app()->tags->deleteTagGroupById($tagGroup->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate taggroup.
     *
     * @param TagGroupModel $tagGroup
     * @param array         $tagGroupDefinition
     * @param string        $tagGroupHandle
     */
    private function populateTagGroup(TagGroupModel $tagGroup, array $tagGroupDefinition, $tagGroupHandle)
    {
        $tagGroup->setAttributes([
            'handle' => $tagGroupHandle,
            'name' => $tagGroupDefinition['name'],
        ]);

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($tagGroupDefinition['fieldLayout']);
        $tagGroup->setFieldLayout($fieldLayout);
    }
}
