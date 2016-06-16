<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseApplicationComponent as BaseApplication;
use Craft\IOHelper;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Models\Result;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic extends BaseApplication
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
    private function getPluginData(array $data, $handle, array $default = [])
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
     * @return Result
     */
    public function importFromYaml($file, $override = null, $force = false)
    {
        Craft::app()->config->maxPowerCaptain();
        Craft::app()->setComponent('userSession', $this);

        $yaml = IOHelper::getFileContents($file);
        $yaml_override = IOHelper::getFileContents($override);
        $dataModel = Data::fromYaml($yaml, $yaml_override);

        return $this->importDataModel($dataModel, $force);
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return Result
     */
    public function exportToYaml($file, $autoCreate = true)
    {
        Craft::app()->config->maxPowerCaptain();
        Craft::app()->setComponent('userSession', $this);

        $result = new Result();
        $dataModel = $this->exportDataModel();
        $yaml = Data::toYaml($dataModel);

        if (!IOHelper::writeToFile($file, $yaml, $autoCreate)) { // Do not auto create
            $result->addError('errors', "Failed to write contents to \"$file\"");
        }

        return $result;
    }

    /**
     * Import data model.
     *
     * @param Data $model
     * @param bool $force if set to true items not in the import will be deleted
     *
     * @return Result
     */
    private function importDataModel(Data $model, $force)
    {
        // Import schema
        $localesImportResult = Craft::app()->schematic_locales->import($model->getAttribute('locales', $force));
        $pluginImportResult = Craft::app()->schematic_plugins->import($model->getAttribute('plugins', $force));
        $fieldImportResult = Craft::app()->schematic_fields->import($model->getAttribute('fields'), $force);
        $assetSourcesImportResult = Craft::app()->schematic_assetSources->import($model->getAttribute('assetSources'), $force);
        $globalSetsImportResult = Craft::app()->schematic_globalSets->import($model->getAttribute('globalSets'), $force);
        $sectionImportResult = Craft::app()->schematic_sections->import($model->getAttribute('sections'), $force);
        $categoryGroupImportResult = Craft::app()->schematic_categoryGroups->import($model->getAttribute('categoryGroups'), $force);
        $tagGroupImportResult = Craft::app()->schematic_tagGroups->import($model->getAttribute('tagGroups'), $force);
        $userGroupImportResult = Craft::app()->schematic_userGroups->import($model->getAttribute('userGroups'), $force);
        $userImportResult = Craft::app()->schematic_users->import($model->getAttribute('users'), true);
        $fieldImportResultFinal = Craft::app()->schematic_fields->import($model->getAttribute('fields'), $force);

        // Element index settings are supported from Craft 2.5
        if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $elementIndexSettingsImportResult = Craft::app()->schematic_elementIndexSettings->import($model->getAttribute('elementIndexSettings'), $force);
        }

        // Verify results
        $result = new Result();
        $result->consume($localesImportResult);
        $result->consume($pluginImportResult);
        $result->consume($fieldImportResult);
        $result->consume($assetSourcesImportResult);
        $result->consume($globalSetsImportResult);
        $result->consume($sectionImportResult);
        $result->consume($categoryGroupImportResult);
        $result->consume($tagGroupImportResult);
        $result->consume($userGroupImportResult);
        $result->consume($userImportResult);
        $result->consume($fieldImportResultFinal);

        // Element index settings are supported from Craft 2.5
        if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $result->consume($elementIndexSettingsImportResult);
        }

        $services = Craft::app()->plugins->call('registerMigrationService');
        $this->doImport($result, $model->pluginData, $services, $force);

        return $result;
    }

    /**
     * Handles importing.
     *
     * @param Result       $result
     * @param array        $data
     * @param array|Base[] $services
     * @param bool         $force
     */
    private function doImport(Result $result, array $data, $services, $force)
    {
        foreach ($services as $handle => $service) {
            if (is_array($service)) {
                $this->doImport($result, $data, $service, $force);
            } elseif ($service instanceof Base) {
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
        $fieldGroups = Craft::app()->fields->getAllGroups();
        $sections = Craft::app()->sections->getAllSections();
        $globals = Craft::app()->globals->getAllSets();
        $userGroups = Craft::app()->userGroups->getAllGroups();
        $categoryGroups = Craft::app()->categories->getAllGroups();
        $tagGroups = Craft::app()->tags->getAllTagGroups();

        $export = [
            'locales' => Craft::app()->schematic_locales->export(),
            'assetSources' => Craft::app()->schematic_assetSources->export(),
            'fields' => Craft::app()->schematic_fields->export($fieldGroups),
            'plugins' => Craft::app()->schematic_plugins->export(),
            'sections' => Craft::app()->schematic_sections->export($sections),
            'globalSets' => Craft::app()->schematic_globalSets->export($globals),
            'userGroups' => Craft::app()->schematic_userGroups->export($userGroups),
            'users' => Craft::app()->schematic_users->export(),
            'categoryGroups' => Craft::app()->schematic_categoryGroups->export($categoryGroups),
            'tagGroups' => Craft::app()->schematic_tagGroups->export($tagGroups),
        ];

        // Element index settings are supported from Craft 2.5
        if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $export['elementIndexSettings'] = Craft::app()->schematic_elementIndexSettings->export();
        }

        $export['pluginData'] = [];
        $services = Craft::app()->plugins->call('registerMigrationService');
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
            } elseif ($service instanceof Base) {
                if ($service instanceof Base) {
                    $data[$handle] = $service->export();
                }
            }
        }
    }

    /**
     * Assume schematic can do anything.
     *
     * @return bool
     */
    public function checkPermission()
    {
        return true;
    }
}
