<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Models\Result;

/**
 * Schematic Base Service for some easy access methods.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
abstract class Base extends BaseComponent
{
    /**
     * @var Result
     */
    protected $resultModel;

    /**
     * Required import method.
     *
     * @param array $data
     * @param bool  $force
     *
     * @return Result
     */
    abstract public function import(array $data, $force);

    /**
     * Required export method.
     *
     * @param array|null $data
     *
     * @return mixed
     */
    abstract public function export();

    /**
     * Get all record definitions
     *
     * @param  array  $records
     * @return array
     */
    protected function getRecordDefinitions(array $records)
    {
        $result = [];
        foreach ($records as $record) {
            $result[$record->handle] = $this->getRecordDefinition($record);
        }
        return $result;
    }

    /**
     * Get single record definition
     * @param  Model  $record
     * @return
     */
    protected function getRecordDefinition(Model $record)
    {
        $attributes = $record->attributes;
        unset($attributes['id']);
        return $attributes;
    }
}
