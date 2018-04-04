<?php

namespace NerdsAndCompany\Schematic\Services;

use craft\base\Model;
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
     * Check required properties
     */
    public function __construct()
    {
        if (!isset($this->recordClass)) {
            throw new LogicException(get_class($this) . ' must have a $recordClass');
        }
    }

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
        $attributes = $record->attributes;
        unset($attributes['id']);
        unset($attributes['dateCreated']);
        unset($attributes['dateUpdated']);

        if (isset($attributes['sources'])) {
            $attributes['sources'] = $this->getSources(get_class($record), $attributes['sources'], 'id', 'handle');
        }

        if (isset($attributes['source'])) {
            $attributes['source'] = $this->getSource(get_class($record), $attributes['sources'], 'id', 'handle');
        }

        if (isset($attributes['fieldLayoutId'])) {
            $attributes['fieldLayout'] = $this->getFieldLayoutDefinition($record->getFieldLayout());
            unset($attributes['fieldLayoutId']);
        }

        return $attributes;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Import asset volumes.
     *
     * @param array $definitions
     * @param bool  $force
     */
    public function import(array $definitions, $force = false)
    {
        $recordsByHandle = [];
        foreach ($this->getRecords() as $record) {
            $recordsByHandle[$record->handle] = $record;
        }

        foreach ($definitions as $handle => $definition) {
            $record = new $this->recordClass();
            if (array_key_exists($handle, $recordsByHandle)) {
                $record = $recordsByHandle[$handle];
            }
            $record->setAttributes($definition);
            Schematic::info('Importing record '.$handle);
            if (!$this->saveRecord($record)) {
                Schematic::warning('Error importing record '.$handle);
                foreach ($record->getErrors() as $errors) {
                    foreach ($errors as $error) {
                        Schematic::error($error);
                    }
                }
            }
            unset($recordsByHandle[$handle]);
        }

        if ($force) {
            // Delete volumes not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                Schematic::info('Deleting record '.$handle);
                $this->deleteRecord($record);
            }
        }
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @return boolean
     */
    abstract protected function saveRecord(Model $record);

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    abstract protected function deleteRecord(Model $record);
}
