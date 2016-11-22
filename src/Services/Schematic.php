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
 * @copyright Copyright (c) 2015-2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic extends BaseApplication
{
    const SCHEMATIC_METHOD_IMPORT = 'import';
    const SCHEMATIC_METHOD_EXPORT = 'export';

    protected static $exportableDataTypes = [
        'locales',
        'assetSources',
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
     * @param Data $model
     * @param bool $force if set to true items not in the import will be deleted
     *
     * @return Result
     */
    private function importDataModel(Data $model, $force)
    {
        // Import schema
        $locales = $model->getAttribute('locales', $force);
        $localesImportResult = Craft::app()->schematic_locales->import($locales);

        $plugins = $model->getAttribute('plugins', $force);
        $pluginImportResult = Craft::app()->schematic_plugins->import($plugins);

        $fields = $model->getAttribute('fields');
        $fieldImportResult = Craft::app()->schematic_fields->import($fields, $force);

        $assetSources = $model->getAttribute('assetSources');
        $assetSourcesImportResult = Craft::app()->schematic_assetSources->import($assetSources, $force);

        $globalSets = $model->getAttribute('globalSets');
        $globalSetsImportResult = Craft::app()->schematic_globalSets->import($globalSets, $force);

        $sections = $model->getAttribute('sections');
        $sectionImportResult = Craft::app()->schematic_sections->import($sections, $force);

        $categoryGroups = $model->getAttribute('categoryGroups');
        $categoryGroupImportResult = Craft::app()->schematic_categoryGroups->import($categoryGroups, $force);

        $tagGroups = $model->getAttribute('tagGroups');
        $tagGroupImportResult = Craft::app()->schematic_tagGroups->import($tagGroups, $force);

        $userGroups = $model->getAttribute('userGroups');
        $userGroupImportResult = Craft::app()->schematic_userGroups->import($userGroups, $force);

        $users = $model->getAttribute('users');
        $userImportResult = Craft::app()->schematic_users->import($users, true);

        $fields = $model->getAttribute('fields');
        $fieldImportResultFinal = Craft::app()->schematic_fields->import($fields, $force);

        // Element index settings are supported from Craft 2.5
        if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $elementIndexSettingsImportResult = Craft::app()->schematic_elementIndexSettings->import(
                $model->getAttribute('elementIndexSettings'),
                $force
            );
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

        $categoryGroups = Craft::app()->categories->getAllGroups();
        $tagGroups = Craft::app()->tags->getAllTagGroups();

        $export = [];

        if (in_array('locales', $dataTypes)) {
            $export['locales'] = Craft::app()->schematic_locales->export();
        }

        if (in_array('assetSources', $dataTypes)) {
            $export['assetSources'] = Craft::app()->schematic_assetSources->export();
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
