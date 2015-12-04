<?php

namespace Craft;

/**
 * Schematic Export Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class ExportCommand extends BaseCommand
{
    /**
     * Exports the Craft datamodel.
     *
     * @param string $file file to write the schema to
     *
     * @return int
     */
    public function actionIndex($file = 'craft/config/schema.yml')
    {
        craft()->schematic->exportToYaml($file);

        Craft::log(Craft::t('Exported schema to {file}', array('file' => $file)));

        return 0;
    }
}
