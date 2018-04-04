<?php

namespace NerdsAndCompany\Schematic\Services;

use craft\base\Model;
use craft\helpers\ArrayHelper;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;
use LogicException;

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
     * Load fieldlayout and sources behaviors
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
     * Get all records
     *
     * @return Model[]
     */
    abstract protected function getRecords();

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all record definitions
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
     * Get single record definition
     *
     * @param  Model $record
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

        if (isset($definition['attributes']['sources'])) {
            $definition['sources'] = $this->getSources($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        if (isset($definition['attributes']['source'])) {
            $definition['source'] = $this->getSource($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        if (isset($definition['attributes']['fieldLayoutId'])) {
            $definition['fieldLayout'] = $this->getFieldLayoutDefinition($record->getFieldLayout());
            unset($definition['attributes']['fieldLayoutId']);
        }

        return $definition;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Import asset volumes.
     *
     * @param array $definitions
     * @param Model $records The existing records
     * @param array $defaultAttributes Default attributes to use for each record
     */
    public function import(array $definitions, array $records = [], array $defaultAttributes = [])
    {
        $records = $records ?: $this->getRecords();
        $recordsByHandle = ArrayHelper::index($records, 'handle');
        foreach ($definitions as $handle => $definition) {
            $record = new $definition['class']();
            if (array_key_exists($handle, $recordsByHandle)) {
                $record = $recordsByHandle[$handle];
            }
            Schematic::info('- Saving '.get_class($record).' '.$handle);
            $this->setRecordAttributes($record, $definition, $defaultAttributes);
            if (!$this->saveRecord($record, $definition)) {
                $this->importError($record, $handle);
            }
            unset($recordsByHandle[$handle]);
        }

        if (Schematic::$force) {
            // Delete volumes not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                Schematic::info('- Deleting '.get_class($record).' '.$handle);
                $this->deleteRecord($record);
            }
        }
    }

    /**
     * Log an import error
     *
     * @param  Model $record
     * @param  string $handle
     */
    protected function importError($record, $handle)
    {
        Schematic::warning('- Error importing record '.$handle);
        foreach ($record->getErrors() as $errors) {
            foreach ($errors as $error) {
                Schematic::error('   - '.$error);
            }
        }
    }

    /**
     * Set record attributes from definition
     *
     * @param Model $record
     * @param array $definition
     */
    private function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes)
    {
        $attributes = array_merge($definition['attributes'], $defaultAttributes);
        $record->setAttributes($attributes);

        if (array_key_exists('fieldLayout', $definition)) {
            $record->setFieldLayout($this->getFieldLayout($definition['fieldLayout']));
        }
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @param array $definition
     * @return boolean
     */
    abstract protected function saveRecord(Model $record, array $definition);

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    abstract protected function deleteRecord(Model $record);
}
