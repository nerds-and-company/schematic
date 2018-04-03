<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use Craft\TagGroupModel;

/**
 * Schematic TagGroups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TagGroups extends Base
{
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all tag groups
     *
     * @return TagGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->tags->getAllTagGroups();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Attempt to import tagGroups.
     *
     * @param array $tagGroupDefinitions
     * @param bool  $force               If set to true tagGroups not included in the import will be deleted
     *
     * @return Result
     */
    public function import($force = false, array $tagGroupDefinitions = null)
    {
        Craft::info('Importing TagGroups', 'schematic');

        $tagGroups = Craft::$app->tags->getAllTagGroups('handle');

        foreach ($tagGroupDefinitions as $tagGroupHandle => $tagGroupDefinition) {
            $tagGroup = array_key_exists($tagGroupHandle, $tagGroups)
                ? $tagGroups[$tagGroupHandle]
                : new TagGroupModel();

            unset($tagGroups[$tagGroupHandle]);

            $this->populateTagGroup($tagGroup, $tagGroupDefinition, $tagGroupHandle);

            if (!Craft::$app->tags->saveTagGroup($tagGroup)) { // Save taggroup via craft
                $this->addErrors($tagGroup->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($tagGroups as $tagGroup) {
                Craft::$app->tags->deleteTagGroupById($tagGroup->id);
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

        $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($tagGroupDefinition['fieldLayout']);
        $tagGroup->setFieldLayout($fieldLayout);
    }
}
