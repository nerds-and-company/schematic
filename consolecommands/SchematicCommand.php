<?php

namespace Craft;

/**
 * Schematic Command.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class SchematicCommand extends BaseCommand
{
    /**
     * Imports the Craft datamodel.
     *
     * @param string $file  yml file containing the schema definition
     * @param bool   $force if set to true items not in the import will be deleted
     */
    public function actionImport($file = 'craft/config/schema.yml', $force = false)
    {
        if (!IOHelper::fileExists($file)) {
            $this->usageError(Craft::t('File not found.'));
        }

        $result = craft()->schematic->importFromYaml($file, $force);

        if (!$result->hasErrors()) {
            SchematicPlugin::log(Craft::t('Loaded schema from {file}', array('file' => $file)));

            return 0;
        }

        SchematicPlugin::log(Craft::t('There was an error loading schema from {file}', array('file' => $file)));
        print_r($result->getErrors());

        return 1;
    }

    /**
     * Exports the Craft datamodel.
     *
     * @param string $file file to write the schema to
     */
    public function actionExport($file = 'craft/config/schema.yml')
    {
        craft()->schematic->exportToYaml($file);

        SchematicPlugin::log(Craft::t('Exported schema to {file}', array('file' => $file)));

        return 0;
    }
}
