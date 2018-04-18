<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Base\Field;

/**
 * Schematic Asset Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Assets extends Field
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['targetSiteId']);

        if (isset($definition['attributes']['defaultUploadLocationSource'])) {
            $definition['attributes']['defaultUploadLocationSource'] = $this->getSource(
                $definition['class'],
                $definition['attributes']['defaultUploadLocationSource'],
                'id',
                'handle'
            );
        }

        if (isset($definition['attributes']['singleUploadLocationSource'])) {
            $definition['attributes']['singleUploadLocationSource'] = $this->getSource(
                $definition['class'],
                $definition['attributes']['singleUploadLocationSource'],
                'id',
                'handle'
            );
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes)
    {
        if (isset($definition['attributes']['defaultUploadLocationSource'])) {
            $definition['attributes']['defaultUploadLocationSource'] = $this->getSource(
                $definition['class'],
                $definition['attributes']['defaultUploadLocationSource'],
                'handle',
                'id'
            );
        }

        if (isset($definition['attributes']['singleUploadLocationSource'])) {
            $definition['attributes']['singleUploadLocationSource'] = $this->getSource(
                $definition['class'],
                $definition['attributes']['singleUploadLocationSource'],
                'handle',
                'id'
            );
        }

        parent::setRecordAttributes($record, $definition, $defaultAttributes);
    }
}
