<?php

namespace Craft;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class SchematicService extends BaseApplicationComponent
{
    /**
     * Import from JSON.
     *
     * @param string $json
     * @param bool   $force if set to true items not included in import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function importFromJson($json, $force = false)
    {
        $exportedDataModel = Schematic_ExportedDataModel::fromJson($json);

        return $this->importFromExportedDataModel($exportedDataModel, $force);
    }

    /**
     * Load from JSON.
     *
     * @param string $json
     *
     * @return string
     */
    public function loadFromJson($json)
    {
        $data = Schematic_ExportedDataModel::fromJson($json);

        return $data;
    }

    /**
     * Import from array.
     *
     * @param array $array
     * @param bool  $force if set to true items not included in import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function importFromArray(array $array, $force = false)
    {
        $exportedDataModel = new Schematic_ExportedDataModel($array);

        return $this->importFromExportedDataModel($exportedDataModel, $force);
    }

    /**
     * Import from exported data model.
     *
     * @param Schematic_ExportedDataModel $model
     * @param bool                        $force if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    private function importFromExportedDataModel(Schematic_ExportedDataModel $model, $force)
    {
        $result = new Schematic_ResultModel();

        if ($model !== null) {
            $pluginImportResult = craft()->schematic_plugins->import($model->plugins);
            $assetImportResult = craft()->schematic_assets->import($model->assets);
            $fieldImportResult = craft()->schematic_fields->import($model->fields, $force);
            $globalImportResult = craft()->schematic_globals->import($model->globals, $force);
            $sectionImportResult = craft()->schematic_sections->import($model->sections, $force);
            $userGroupImportResult = craft()->schematic_userGroups->import($model->userGroups, $force);

            $result->consume($pluginImportResult);
            $result->consume($assetImportResult);
            $result->consume($fieldImportResult);
            $result->consume($globalImportResult);
            $result->consume($sectionImportResult);
            $result->consume($userGroupImportResult);

            // run plugin imports through hook
            $services = craft()->plugins->callFirst('registerMigrationService');
            if (is_array($services)) {
                foreach ($services as $handle => $service) {
                    $hookResult = $service->import($model->pluginData[$handle], $force);
                    $result->consume($hookResult);
                }
            }
        }

        return $result;
    }

    /**
     * Export.
     *
     * @return array
     */
    public function export()
    {
        $fieldGroups = craft()->fields->getAllGroups();
        $sections = craft()->sections->getAllSections();
        $globals = craft()->globals->getAllSets();
        $userGroups = craft()->userGroups->getAllGroups();

        $export = array(
            'assets' => craft()->schematic_assets->export(),
            'fields' => craft()->schematic_fields->export($fieldGroups),
            'plugins' => craft()->schematic_plugins->export(),
            'sections' => craft()->schematic_sections->export($sections),
            'globals' => craft()->schematic_globals->export($globals),
            'userGroups' => craft()->schematic_userGroups->export($userGroups),
        );

        // run plugin exports through hook
        $services = craft()->plugins->callFirst('registerMigrationService');
        if (is_array($services)) {
            $export['pluginData'] = array();
            foreach ($services as $handle => $service) {
                $export['pluginData'][$handle] = $service->export();
            }
        }

        return $export;
    }
}
