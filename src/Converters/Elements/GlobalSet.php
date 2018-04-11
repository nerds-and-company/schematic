<?php

namespace NerdsAndCompany\Schematic\Converters\Elements;

use Craft;
use craft\elements\GlobalSet as GlobalSetElement;
use craft\base\Model;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Globals Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSet extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record instanceof GlobalSetElement) {
            $definition['site'] = $record->getSite()->handle;
            unset($definition['attributes']['tempId']);
            unset($definition['attributes']['uid']);
            unset($definition['attributes']['contentId']);
            unset($definition['attributes']['siteId']);
            unset($definition['attributes']['hasDescendants']);
            unset($definition['attributes']['ref']);
            unset($definition['attributes']['status']);
            unset($definition['attributes']['totalDescendants']);
            unset($definition['attributes']['url']);

            foreach ($record->getFieldLayout()->getFields() as $field) {
                unset($definition['attributes'][$field->handle]);
            }
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        if (array_key_exists('site', $definition)) {
            $site = Craft::$app->sites->getSiteByHandle($definition['site']);
            if ($site) {
                $record->siteId = $site->id;
            } else {
                Schematic::error('Site '.$definition['site'].' could not be found');

                return false;
            }
        }

        return Craft::$app->globals->saveSet($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->elements->deleteElementById($record->id);
    }
}
