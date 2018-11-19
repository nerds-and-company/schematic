<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use craft\base\Model;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;

/**
 * Schematic Model Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ModelMapper extends BaseComponent implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(array $records): array
    {
        $result = [];
        foreach ($records as $record) {
            $modelClass = get_class($record);
            $converter = Craft::$app->controller->module->getConverter($modelClass);
            if ($converter) {
                $index = $converter->getRecordIndex($record);
                $result[$index] = $converter->getRecordDefinition($record);
            }
        }

        return $result;
    }

    /**
     * Import records.
     *
     * @param array $definitions
     * @param Model $records           The existing records
     * @param array $defaultAttributes Default attributes to use for each record
     * @param bool  $persist           Whether to persist the parsed records
     *
     * @return array
     */
    public function import(array $definitions, array $records, array $defaultAttributes = [], $persist = true): array
    {
        $imported = [];
        $recordsByHandle = $this->getRecordsByHandle($records);
        foreach ($definitions as $handle => $definition) {
            $modelClass = $definition['class'];
            $converter = Craft::$app->controller->module->getConverter($modelClass);
            if ($converter) {
                $record = $this->findOrNewRecord($recordsByHandle, $definition, $handle);

                if ($converter->getRecordDefinition($record) === $definition) {
                    Schematic::info('- Skipping '.get_class($record).' '.$handle);
                } else {
                    $converter->setRecordAttributes($record, $definition, $defaultAttributes);
                    if ($persist) {
                        Schematic::info('- Saving '.get_class($record).' '.$handle);
                        if (!$converter->saveRecord($record, $definition)) {
                            Schematic::importError($record, $handle);
                        }
                    }
                }

                $imported[] = $record;
            }
            unset($recordsByHandle[$handle]);
        }

        if (Schematic::$force && $persist) {
            // Delete records not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                $modelClass = get_class($record);
                Schematic::info('- Deleting '.get_class($record).' '.$handle);
                $converter = Craft::$app->controller->module->getConverter($modelClass);
                $converter->deleteRecord($record);
            }
        }

        return $imported;
    }

    /**
     * Get records by handle.
     *
     * @param array $records
     *
     * @return array
     */
    private function getRecordsByHandle(array $records): array
    {
        $recordsByHandle = [];
        foreach ($records as $record) {
            $modelClass = get_class($record);
            $converter = Craft::$app->controller->module->getConverter($modelClass);
            $index = $converter->getRecordIndex($record);
            $recordsByHandle[$index] = $record;
        }

        return $recordsByHandle;
    }

    /**
     * Find record from records by handle or new record.
     *
     * @param Model[] $recordsByHandle
     * @param array   $definition
     * @param string  $handle
     *
     * @return Model
     */
    private function findOrNewRecord(array $recordsByHandle, array $definition, string $handle): Model
    {
        $record = new $definition['class']();
        if (array_key_exists($handle, $recordsByHandle)) {
            $existing = $recordsByHandle[$handle];
            if (get_class($record) == get_class($existing)) {
                $record = $existing;
            } else {
                $record->id = $existing->id;
                $record->setAttributes($existing->getAttributes());
            }
        }

        return $record;
    }
}
