<?php

namespace NerdsAndCompany\Schematic\Services;

use \Craft;
use craft\base\VolumeInterface;
use craft\volumes\Local;

/**
 * Schematic Asset Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Volumes extends Base
{
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all asset transforms
     *
     * @return VolumeInterface[]
     */
    protected function getRecords()
    {
        return Craft::$app->volumes->getAllVolumes();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================


    /**
     * Import asset volumes.
     *
     * @TODO Export volume class
     *
     * @param array $volumeDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $volumeDefinitions, $force = false)
    {
        $recordsByHandle = [];
        foreach ($this->getRecords() as $record) {
            $recordsByHandle[$record->handle] = $record;
        }

        foreach ($volumeDefinitions as $handle => $definition) {
            $record = new Local();
            if (array_key_exists($handle, $recordsByHandle)) {
                $record = $recordsByHandle[$handle];
            }
            $record->setAttributes($definition);
            if (Craft::$app->volumes->saveVolume($record)) {
                Craft::info('Imported volume '.$handle, 'schematic');
            } else {
                Craft::warning('Error importing volume '.$handle, 'schematic');
                foreach ($record->getErrors() as $errors) {
                    foreach ($errors as $error) {
                        var_dump($error);
                        Craft::error($error, 'schematic');
                    }
                }
            }
            unset($recordsByHandle[$handle]);
        }

        if ($force) {
            // Delete volumes not in definitions
            foreach ($recordsByHandle as $handle => $record) {
                Craft::info('Deleting volume '.$handle, 'schematic');
                Craft::$app->volumes->deleteVolume($record);
            }
        }
    }
}
