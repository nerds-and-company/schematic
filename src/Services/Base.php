<?php

namespace NerdsAndCompany\Schematic\Services;

use craft\base\Model;
use craft\helpers\ArrayHelper;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Base Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
abstract class Base extends BaseComponent implements MappingInterface
{
    /**
     * Load fieldlayout and sources behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          FieldLayoutBehavior::className(),
          SourcesBehavior::className(),
        ];
    }

    /**
     * Get all records.
     *
     * @return Model[]
     */
    abstract protected function getRecords();

    /**
     * Get all record definitions.
     *
     * @return array
     */
    public function export(array $records = null)
    {
        $records = $records ?: $this->getRecords();
        $result = [];
        foreach ($records as $record) {
            $result[$record->handle] = $this->getRecordDefinition($record);
        }

        return $result;
    }

    /**
     * Get single record definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = [
          'class' => get_class($record),
          'attributes' => $record->attributes,
        ];
        unset($definition['attributes']['id']);
        unset($definition['attributes']['dateCreated']);
        unset($definition['attributes']['dateUpdated']);

        // Define sources
        if (isset($definition['attributes']['sources'])) {
            $definition['sources'] = $this->getSources($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        if (isset($definition['attributes']['source'])) {
            $definition['source'] = $this->getSource($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        // Define field layout
        if (isset($definition['attributes']['fieldLayoutId'])) {
            $definition['fieldLayout'] = $this->getFieldLayoutDefinition($record->getFieldLayout());
            unset($definition['attributes']['fieldLayoutId']);
        }

        // Define site settings
        if (isset($record->siteSettings)) {
            $definition['siteSettings'] = [];
            foreach ($record->getSiteSettings() as $siteSetting) {
                $definition['siteSettings'][$siteSetting->site->handle] = $this->getRecordDefinition($siteSetting);
            }
        }

        return $definition;
    }

    /**
     * Import records.
     *
     * @param array $definitions
     * @param Model $records           The existing records
     * @param array $defaultAttributes Default attributes to use for each record
     */
    public function import(array $definitions, array $records = null, array $defaultAttributes = [])
    {
        $records = $records ?: $this->getRecords();
        $recordsByHandle = ArrayHelper::index($records, 'handle');
        foreach ($definitions as $handle => $definition) {
            $record = new $definition['class']();
            if (array_key_exists($handle, $recordsByHandle)) {
                $record = $recordsByHandle[$handle];
                if ($this->getRecordDefinition($record) === $definition) {
                    Schematic::info('- Skipping '.get_class($record).' '.$handle);
                    unset($recordsByHandle[$handle]);
                    continue;
                }
            }

            Schematic::info('- Saving '.get_class($record).' '.$handle);
            $this->setRecordAttributes($record, $definition, $defaultAttributes);
            if (!$this->saveRecord($record, $definition)) {
                $this->importError($record, $handle);
            }
            unset($recordsByHandle[$handle]);
        }

        if (Schematic::$force) {
            // Delete records not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                Schematic::info('- Deleting '.get_class($record).' '.$handle);
                $this->deleteRecord($record);
            }
        }
    }

    /**
     * Log an import error.
     *
     * @param Model  $record
     * @param string $handle
     */
    protected function importError($record, $handle)
    {
        Schematic::warning('- Error importing '.get_class($record).' '.$handle);
        foreach ($record->getErrors() as $errors) {
            foreach ($errors as $error) {
                Schematic::error('   - '.$error);
            }
        }
    }

    /**
     * Set record attributes from definition.
     *
     * @param Model $record
     * @param array $definition
     */
    private function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes)
    {
        $attributes = array_merge($definition['attributes'], $defaultAttributes);
        $record->setAttributes($attributes);

        // Set field layout
        if (array_key_exists('fieldLayout', $definition)) {
            $record->setFieldLayout($this->getFieldLayout($definition['fieldLayout']));
        }

        // Set site settings
        if (array_key_exists('siteSettings', $definition)) {
            $siteSettings = [];
            foreach ($definition['siteSettings'] as $handle => $siteSettingDefinition) {
                $siteSetting = new $siteSettingDefinition['class']($siteSettingDefinition['attributes']);
                $site = Craft::$app->sites->getSiteByHandle($handle);
                if ($site) {
                    $siteSetting->siteId = $site->id;
                    $siteSettings[] = $siteSetting;
                } else {
                    Schematic::warning('  - Site '.$handle.' could not be found');
                }
            }
            $record->setSiteSettings($siteSettings);
        }
    }

    /**
     * Save a record.
     *
     * @param Model $record
     * @param array $definition
     *
     * @return bool
     */
    abstract protected function saveRecord(Model $record, array $definition);

    /**
     * Delete a record.
     *
     * @param Model $record
     *
     * @return bool
     */
    abstract protected function deleteRecord(Model $record);
}
