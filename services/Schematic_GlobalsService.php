<?php

namespace Craft;

/**
 * Schematic Globals Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_GlobalsService extends BaseApplicationComponent
{
    /**
     * Export globalsets.
     *
     * @param GlobalSetModel[] $globalSets
     *
     * @return array
     */
    public function export(array $globalSets)
    {
        $globalDefinitions = array();

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
        return array(
            'name' => $globalSet->name,
            'fieldLayout' => craft()->schematic_fields->getFieldLayoutDefinition($globalSet->getFieldLayout()),
        );
    }

    /**
     * Attempt to import globals.
     *
     * @param array $globalSetDefinitions
     * @param bool  $force                If set to true globals not included in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import($globalSetDefinitions, $force = false)
    {
        $result = new Schematic_ResultModel();

        if (empty($globalSetDefinitions)) {
            // Ignore importing globals.
            return $result;
        }

        $globalSets = craft()->globals->getAllSets('handle');

        foreach ($globalSetDefinitions as $globalSetHandle => $globalSetDefinition) {
            $global = array_key_exists($globalSetHandle, $globalSets)
                ? $globalSets[$globalSetHandle]
                : new GlobalSetModel();

            $this->populateGlobalSet($global, $globalSetDefinition, $globalSetHandle);

            // Save globalset via craft
            if (!craft()->globals->saveSet($global)) {
                return $result->error($global->getAllErrors());
            }
            unset($globalSets[$globalSetHandle]);
        }

        if ($force) {
            foreach ($globalSets as $globalSet) {
                craft()->globals->deleteSetById($globalSet->id);
            }
        }

        return $result;
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
        $globalSet->setAttributes(array(
            'handle' => $globalSetHandle,
            'name' => $globalSetDefinition['name'],
        ));

        $fieldLayout = craft()->schematic_fields->getFieldLayout($globalSetDefinition['fieldLayout']);
        $globalSet->setFieldLayout($fieldLayout);
    }
}
