<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseApplicationComponent as BaseApplication;
use Craft\Exception;
use Craft\IOHelper;
use NerdsAndCompany\Schematic\Models\Data;
use NerdsAndCompany\Schematic\Models\Result;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Schematic extends BaseApplication
{
    const SCHEMATIC_METHOD_IMPORT = 'import';
    const SCHEMATIC_METHOD_EXPORT = 'export';

    protected static $exportableDataTypes = [
        'locales',
        'assetSources',
        'assetTransforms',
        'fields',
        'plugins',
        'sections',
        'globalSets',
        'userGroups',
        'users',
        'categoryGroups',
        'tagGroups',
        'elementIndexSettings',
        'pluginData',
    ];

    public static function getExportableDataTypes()
    {
        return self::$exportableDataTypes;
    }

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
     * @param bool   $force if set to true items not included in import will be deleted
     * @param string $dataTypes The data types to import
     *
     * @return Result
     * @throws Exception
     */
    public function importFromYaml($file, $override = null, $force = false, $dataTypes = 'all')
    {
        Craft::app()->config->maxPowerCaptain();
        Craft::app()->setComponent('userSession', $this);

        $yaml = IOHelper::getFileContents($file);
        $yaml_override = IOHelper::getFileContents($override);
        $dataModel = Data::fromYaml($yaml, $yaml_override);

        return $this->importDataModel($dataModel, $force, $dataTypes);
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return Result
     */
    public function exportToYaml($file, $dataTypes = 'all', $autoCreate = true)
    {
        Craft::app()->config->maxPowerCaptain();
        Craft::app()->setComponent('userSession', $this);

        $result = new Result();
        $dataModel = $this->exportDataModel($dataTypes);
        $yaml = Data::toYaml($dataModel);

        if (!IOHelper::writeToFile($file, $yaml, $autoCreate)) { // Do not auto create
            $result->addError('errors', "Failed to write contents to \"$file\"");
        }

        return $result;
    }

    /**
     * Import data model.
     *
     * @param Data         $model
     * @param bool         $force     if set to true items not in the import will be deleted
     * @param string|array $dataTypes The data types to export
     *
     * @return Result
     * @throws Exception
     */
    private function importDataModel(Data $model, $force, $dataTypes = 'all')
    {
        // If all data types should be imported, get all the available data types that can be imported.
        if (is_array($dataTypes)) {
            // Validate that each data type specified to be imported is reconized.
            foreach ($dataTypes as $dataType) {
                if (!in_array($dataType, $this->exportableDataTypes)) {
                    $errorMessage = 'Invalid export type "'.$dataType.'". Accepted types are '
                                    .implode(', ', $this->exportableDataTypes);
                    throw new Exception($errorMessage);
                }
            }
        } else {
            $dataTypes = $this->exportableDataTypes;
        }

        // Import schema
        if (in_array('locales', $dataTypes)) {
            $locales = $model->getAttribute('locales', $force);
            $localesImportResult = Craft::app()->schematic_locales->import($locales);
        }

        if (in_array('plugins', $dataTypes)) {
            $plugins = $model->getAttribute('plugins', $force);
            $pluginImportResult = Craft::app()->schematic_plugins->import($plugins);
        }

        if (in_array('fields', $dataTypes)) {
            $fields = $model->getAttribute('fields');
            $fieldImportResult = Craft::app()->schematic_fields->import($fields, $force);
        }

        if (in_array('assetSources', $dataTypes)) {
            $assetSources = $model->getAttribute('assetSources');
            $assetSourcesImportResult = Craft::app()->schematic_assetSources->import($assetSources, $force);
        }

        if (in_array('assetTransforms', $dataTypes)) {
            $assetTransforms = $model->getAttribute('assetTransforms');
            $assetTransformsImportResult = Craft::app()->schematic_assetTransforms->import($assetTransforms, $force);
        }

        if (in_array('globalSets', $dataTypes)) {
            $globalSets = $model->getAttribute('globalSets');
            $globalSetsImportResult = Craft::app()->schematic_globalSets->import($globalSets, $force);
        }

        if (in_array('sections', $dataTypes)) {
            $sections = $model->getAttribute('sections');
            $sectionImportResult = Craft::app()->schematic_sections->import($sections, $force);
        }

        if (in_array('categoryGroups', $dataTypes)) {
            $categoryGroups = $model->getAttribute('categoryGroups');
            $categoryGroupImportResult = Craft::app()->schematic_categoryGroups->import($categoryGroups, $force);
        }

        if (in_array('tagGroups', $dataTypes)) {
            $tagGroups = $model->getAttribute('tagGroups');
            $tagGroupImportResult = Craft::app()->schematic_tagGroups->import($tagGroups, $force);
        }

        if (in_array('userGroups', $dataTypes)) {
            $userGroups = $model->getAttribute('userGroups');
            $userGroupImportResult = Craft::app()->schematic_userGroups->import($userGroups, $force);
        }

        if (in_array('users', $dataTypes)) {
            $users = $model->getAttribute('users');
            $userImportResult = Craft::app()->schematic_users->import($users, true);
        }

        if (in_array('elementIndexSettings', $dataTypes)) {
            // Element index settings are supported from Craft 2.5
            if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
                $elementIndexSettingsImportResult = Craft::app()->schematic_elementIndexSettings->import(
                    $model->getAttribute('elementIndexSettings'),
                    $force
                );
            }
        }

        // Verify results
        $result = new Result();
        empty($localesImportResult) ?: $result->consume($localesImportResult);
        empty($pluginImportResult) ?: $result->consume($pluginImportResult);
        empty($fieldImportResult) ?: $result->consume($fieldImportResult);
        empty($assetSourcesImportResult) ?: $result->consume($assetSourcesImportResult);
        empty($assetTransformsImportResult) ?: $result->consume($assetTransformsImportResult);
        empty($globalSetsImportResult) ?: $result->consume($globalSetsImportResult);
        empty($sectionImportResult) ?: $result->consume($sectionImportResult);
        empty($categoryGroupImportResult) ?: $result->consume($categoryGroupImportResult);
        empty($tagGroupImportResult) ?: $result->consume($tagGroupImportResult);
        empty($userGroupImportResult) ?: $result->consume($userGroupImportResult);
        empty($userImportResult) ?: $result->consume($userImportResult);
        empty($fieldImportResultFinal) ?: $result->consume($fieldImportResultFinal);

        // Element index settings are supported from Craft 2.5
        if (!empty($elementIndexSettingsImportResult) && version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $result->consume($elementIndexSettingsImportResult);
        }

        $services = Craft::app()->plugins->call('registerMigrationService');
        $this->doImport($result, $model->pluginData, $services, $force);

        if (in_array('fields', $dataTypes)) {
            $fields = $model->getAttribute('fields');
            $fieldImportResultFinal = Craft::app()->schematic_fields->import($fields, $force);
        }

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
     * @param string|array $dataTypes The data types to export
     *
     * @return array
     *
     * @throws Exception
     */
    private function exportDataModel($dataTypes = 'all')
    {
        // If all data types should be exported, get all the available data types that can be exported.
        if (is_array($dataTypes)) {
            // Validate that each data type specified to be exported is reconized.
            foreach ($dataTypes as $dataType) {
                if (!in_array($dataType, $this->exportableDataTypes)) {
                    $errorMessage = 'Invalid export type "'.$dataType.'". Accepted types are '
                        .implode(', ', $this->exportableDataTypes);
                    throw new Exception($errorMessage);
                }
            }
        } else {
            $dataTypes = $this->exportableDataTypes;
        }

        $assetSources = Craft::app()->assetSources->getAllSources();
        $assetTransforms = Craft::app()->assetTransforms->getAllTransforms();
        $categoryGroups = Craft::app()->categories->getAllGroups();
        $tagGroups = Craft::app()->tags->getAllTagGroups();

        $export = [];

        if (in_array('locales', $dataTypes)) {
            $export['locales'] = Craft::app()->schematic_locales->export();
        }

        if (in_array('assetSources', $dataTypes)) {
            $export['assetSources'] = Craft::app()->schematic_assetSources->export($assetSources);
        }

        if (in_array('assetTransforms', $dataTypes)) {
            $export['assetTransforms'] = Craft::app()->schematic_assetTransforms->export($assetTransforms);
        }

        if (in_array('fields', $dataTypes)) {
            $fieldGroups = Craft::app()->fields->getAllGroups();
            $export['fields'] = Craft::app()->schematic_fields->export($fieldGroups);
        }

        if (in_array('plugins', $dataTypes)) {
            $export['plugins'] = Craft::app()->schematic_plugins->export();
        }

        if (in_array('sections', $dataTypes)) {
            $sections = Craft::app()->sections->getAllSections();
            $export['sections'] = Craft::app()->schematic_sections->export($sections);
        }

        if (in_array('globalSets', $dataTypes)) {
            $globals = Craft::app()->globals->getAllSets();
            $export['globalSets'] = Craft::app()->schematic_globalSets->export($globals);
        }

        if (in_array('userGroups', $dataTypes)) {
            $userGroups = Craft::app()->userGroups->getAllGroups();
            $export['userGroups'] = Craft::app()->schematic_userGroups->export($userGroups);
        }

        if (in_array('users', $dataTypes)) {
            $export['users'] = Craft::app()->schematic_users->export();
        }

        if (in_array('categoryGroups', $dataTypes)) {
            $export['categoryGroups'] = Craft::app()->schematic_categoryGroups->export($categoryGroups);
        }

        if (in_array('tagGroups', $dataTypes)) {
            $export['tagGroups'] = Craft::app()->schematic_tagGroups->export($tagGroups);
        }

        // Element index settings are supported from Craft 2.5
        if (in_array('elementIndexSettings', $dataTypes) && version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $export['elementIndexSettings'] = Craft::app()->schematic_elementIndexSettings->export();
        }

        if (in_array('pluginData', $dataTypes)) {
            $export['pluginData'] = [];
            $services = Craft::app()->plugins->call('registerMigrationService');
            $this->doExport($services, $export['pluginData']);
        }

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
     * Always return the super user.
     *
     * @return Craft\UserModel
     */
    public function getUser()
    {
        return Craft::app()->users->getUserById(1);
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
