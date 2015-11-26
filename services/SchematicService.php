<?php

namespace Craft;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class SchematicService extends BaseApplicationComponent
{
    const SCHEMATIC_METHOD_IMPORT = 'import';
    const SCHEMATIC_METHOD_EXPORT = 'export';

    /**
     * Returns data from import model or default.
     *
     * @param array  $data
     * @param string $handle
     * @param array  $default
     *
     * @return array
     */
    private function getPluginData(array $data, $handle, array $default = array())
    {
        return (array_key_exists($handle, $data) && !is_null($data[$handle])) ? $data[$handle] : $default;
    }

    /**
     * Import from Yaml file.
     *
     * @param string $file
     * @param string $override
     * @param bool   $force    if set to true items not included in import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function importFromYaml($file, $override = null, $force = false)
    {
        craft()->config->maxPowerCaptain();

        $yaml = IOHelper::getFileContents($file);
        $yaml_override = IOHelper::getFileContents($override);
        $dataModel = Schematic_DataModel::fromYaml($yaml, $yaml_override);

        return $this->importDataModel($dataModel, $force);
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return Schematic_ResultModel
     */
    public function exportToYaml($file, $autoCreate = true)
    {
        craft()->config->maxPowerCaptain();

        $result = new Schematic_ResultModel();
        $dataModel = $this->exportDataModel();
        $yaml = Schematic_DataModel::toYaml($dataModel);

        if (!IOHelper::writeToFile($file, $yaml, $autoCreate)) { // Do not auto create
            $result->addError('errors', "Failed to write contents to \"$file\"");
        }

        return $result;
    }

    /**
     * Import data model.
     *
     * @param Schematic_DataModel $model
     * @param bool                $force if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    private function importDataModel(Schematic_DataModel $model, $force)
    {
        // Import schema
        $pluginImportResult = craft()->schematic_plugins->import($model->getAttribute('plugins', $force));
        $assetImportResult = craft()->schematic_assets->import($model->getAttribute('assets'), $force);
        $fieldImportResult = craft()->schematic_fields->import($model->getAttribute('fields'), $force);
        $globalImportResult = craft()->schematic_globals->import($model->getAttribute('globals'), $force);
        $sectionImportResult = craft()->schematic_sections->import($model->getAttribute('sections'), $force);
        $userGroupImportResult = craft()->schematic_userGroups->import($model->getAttribute('userGroups'), $force);
        $userImportResult = craft()->schematic_users->import($model->getAttribute('users'), true);

        // Verify results
        $result = new Schematic_ResultModel();
        $result->consume($pluginImportResult);
        $result->consume($assetImportResult);
        $result->consume($fieldImportResult);
        $result->consume($globalImportResult);
        $result->consume($sectionImportResult);
        $result->consume($userGroupImportResult);
        $result->consume($userImportResult);

        $services = craft()->plugins->call('registerMigrationService');
        $this->doImport($result, $model->pluginData, $services, $force);

        return $result;
    }

    /**
     * Handles importing.
     *
     * @param Schematic_ResultModel             $result
     * @param array                             $data
     * @param array|Schematic_AbstractService[] $services
     * @param $force
     */
    private function doImport(Schematic_ResultModel $result, array $data, $services, $force)
    {
        foreach ($services as $handle => $service) {
            if (is_array($service)) {
                $this->doImport($result, $data, $service, $force);
            } elseif ($service instanceof Schematic_AbstractService) {
                $pluginData = $this->getPluginData($data, $handle);
                $hookResult = $service->import($pluginData, $force);
                $result->consume($hookResult);
            }
        }
    }

    /**
     * Export data model.
     *
     * @return array
     */
    private function exportDataModel()
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
            'users' => craft()->schematic_users->export(),
        );

        $export['pluginData'] = array();
        $services = craft()->plugins->call('registerMigrationService');
        $this->doExport($services, $export['pluginData']);

        return $export;
    }

    /**
     * Handles exporting.
     *
     * @param array $services
     * @param array $data
     */
    private function doExport(array $services, array &$data)
    {
        foreach ($services as $handle => $service) {
            if (is_array($service)) {
                $this->doExport($service, $data);
            } elseif ($service instanceof Schematic_AbstractService) {
                if ($service instanceof Schematic_AbstractService) {
                    $data[$handle] = $service->export();
                }
            }
        }
    }
}
