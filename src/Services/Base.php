<?php

namespace NerdsAndCompany\Schematic\Services;

use craft\base\Model;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;

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

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all records
     *
     * @return Model[]
     */
    abstract protected function getRecords();

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

    public function import($force = false)
    {
    }
}
