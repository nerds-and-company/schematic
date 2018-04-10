<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Base as BaseConverter;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use yii\base\Component as BaseComponent;

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
class ModelProcessor extends BaseComponent implements MappingInterface
{
    /**
     * Get all record definitions.
     *
     * @return array
     */
    public function export(array $records): array
    {
        $result = [];
        foreach ($records as $record) {
            $modelClass = get_class($record);
            $converter = $this->getConverter($modelClass);
            if ($converter == false) {
                Schematic::error('No converter found for '.$modelClass);
                continue;
            }
            $result[$record->handle] = $converter->getRecordDefinition($record);
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
     */
    public function import(array $definitions, array $records, array $defaultAttributes = [], $persist = true): array
    {
        $imported = [];
        $recordsByHandle = ArrayHelper::index($records, 'handle');
        foreach ($definitions as $handle => $definition) {
            $modelClass = $definition['class'];
            $converter = $this->getConverter($modelClass);
            if ($converter == false) {
                Schematic::error('No converter found for '.$modelClass);
                continue;
            }
            $record = $this->findOrNewRecord($recordsByHandle, $definition, $handle);

            if ($converter->getRecordDefinition($record) === $definition) {
                Schematic::info('- Skipping '.get_class($record).' '.$handle);
            } else {
                $converter->setRecordAttributes($record, $definition, $defaultAttributes);
                if ($persist) {
                    Schematic::info('- Saving '.get_class($record).' '.$handle);
                    if ($converter->saveRecord($record, $definition)) {
                    } else {
                        $this->importError($record, $handle);
                    }
                }
            }
            $imported[] = $record;
            unset($recordsByHandle[$handle]);
        }

        if (Schematic::$force && $persist) {
            // Delete records not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                $modelClass = get_class($record);
                Schematic::info('- Deleting '.get_class($record).' '.$handle);
                $converter = $this->getConverter($modelClass);
                $converter->deleteRecord($record);
            }
        }

        return $imported;
    }

    /**
     * Find record from records by handle or new record.
     *
     * @param Model[] $recordsByHandle
     * @param array   $definition
     *
     * @return Model
     */
    private function findOrNewRecord(array $recordsByHandle, array $definition, string $handle): Model
    {
        $record = new  $definition['class']();
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

    /**
     * Find converter class for model.
     *
     * @param string $modelClass
     *
     * @return BaseConverter
     */
    protected function getConverter(string $modelClass)
    {
        if ($modelClass) {
            $converterClass = 'NerdsAndCompany\\Schematic\\Converters\\'.ucfirst(str_replace('craft\\', '', $modelClass));
            if (class_exists($converterClass)) {
                return new $converterClass();
            }

            return $this->getConverter(get_parent_class($modelClass));
        }

        return false;
    }

    /**
     * Log an import error.
     *
     * @param Model  $record
     * @param string $handle
     */
    public function importError(Model $record, string $handle)
    {
        Schematic::warning('- Error importing '.get_class($record).' '.$handle);
        foreach ($record->getErrors() as $errors) {
            foreach ($errors as $error) {
                Schematic::error('   - '.$error);
            }
        }
    }
}
