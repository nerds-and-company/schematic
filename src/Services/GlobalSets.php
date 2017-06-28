<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\GlobalSetModel;

/**
 * Schematic Globals Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSets extends Base
{
    /**
     * Export globalsets.
     *
     * @param GlobalSetModel[] $globalSets
     *
     * @return array
     */
    public function export(array $globalSets = [])
    {
        Craft::log(Craft::t('Exporting Global Sets'));

        $globalDefinitions = [];

        foreach ($globalSets as $globalSet) {
            $globalDefinitions[$globalSet->handle] = $this->getGlobalDefinition($globalSet);
        }

        return $globalDefinitions;
    }

    /**
     * Get global definition.
     *
     * @param GlobalSetModel $globalSet
     *
     * @return array
     */
    private function getGlobalDefinition(GlobalSetModel $globalSet)
    {
        return [
            'name' => $globalSet->name,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($globalSet->getFieldLayout()),
        ];
    }

    /**
     * Attempt to import globals.
     *
     * @param array $globalSetDefinitions
     * @param bool  $force                If set to true globals not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $globalSetDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Global Sets'));

        $globalSets = Craft::app()->globals->getAllSets('handle');

        foreach ($globalSetDefinitions as $globalSetHandle => $globalSetDefinition) {
            $global = array_key_exists($globalSetHandle, $globalSets)
                ? $globalSets[$globalSetHandle]
                : new GlobalSetModel();

            unset($globalSets[$globalSetHandle]);

            $this->populateGlobalSet($global, $globalSetDefinition, $globalSetHandle);

            if (!Craft::app()->globals->saveSet($global)) { // Save globalset via craft
                $this->addErrors($global->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($globalSets as $globalSet) {
                Craft::app()->globals->deleteSetById($globalSet->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate globalset.
     *
     * @param GlobalSetModel $globalSet
     * @param array          $globalSetDefinition
     * @param string         $globalSetHandle
     */
    private function populateGlobalSet(GlobalSetModel $globalSet, array $globalSetDefinition, $globalSetHandle)
    {
        $globalSet->setAttributes([
            'handle' => $globalSetHandle,
            'name' => $globalSetDefinition['name'],
        ]);

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($globalSetDefinition['fieldLayout']);
        $globalSet->setFieldLayout($fieldLayout);
    }
}
