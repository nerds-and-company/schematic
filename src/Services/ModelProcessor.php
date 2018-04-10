<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Base as BaseConverter;

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
    public function export(array $records = [])
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
     */
    public function import(array $definitions, array $records = [], array $defaultAttributes = [], $persist = true)
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

            $record = new $modelClass();
            if (array_key_exists($handle, $recordsByHandle)) {
                $existing = $recordsByHandle[$handle];
                if (get_class($record) == get_class($existing)) {
                    $record = $existing;
                } else {
                    $record->id = $existing->id;
                    $record->setAttributes($existing->getAttributes());
                }

                if ($converter->getRecordDefinition($record) === $definition) {
                    Schematic::info('- Skipping '.get_class($record).' '.$handle);
                    unset($recordsByHandle[$handle]);
                    continue;
                }
            }

            Schematic::info('- Saving '.get_class($record).' '.$handle);
            $converter->setRecordAttributes($record, $definition, $defaultAttributes);
            if (!$persist || $converter->saveRecord($record, $definition)) {
                $imported[] = $record;
            } else {
                $this->importError($record, $handle);
            }
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
    protected function importError(Model $record, string $handle)
    {
        Schematic::warning('- Error importing '.get_class($record).' '.$handle);
        foreach ($record->getErrors() as $errors) {
            foreach ($errors as $error) {
                Schematic::error('   - '.$error);
            }
        }
    }
}
