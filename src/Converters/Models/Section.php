<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;
use craft\models\Section as SectionModel;
use craft\models\Section_SiteSettings;

/**
 * Schematic Sections Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Section extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record instanceof SectionModel) {
            $definition['entryTypes'] = Craft::$app->controller->module->modelMapper->export($record->getEntryTypes());
        }

        if ($record instanceof Section_SiteSettings) {
            unset($definition['attributes']['sectionId']);
            unset($definition['attributes']['siteId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        if (Craft::$app->sections->saveSection($record)) {
            Craft::$app->controller->module->modelMapper->import(
                $definition['entryTypes'],
                $record->getEntryTypes(),
                ['sectionId' => $record->id]
            );

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->sections->deleteSection($record);
    }
}
