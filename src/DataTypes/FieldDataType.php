<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Fields DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class FieldDataType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getMapperHandle(): string
    {
        return 'modelMapper';
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        return Craft::$app->fields->getAllFields();
    }

    /**
     * {@inheritdoc}
     */
    public function afterImport()
    {
        Craft::$app->fields->updateFieldVersion();
        if (Schematic::$force) {
            $this->clearEmptyGroups();
        }
    }

    /**
     * Clear empty field groups
     */
    private function clearEmptyGroups()
    {
        foreach (Craft::$app->fields->getAllGroups() as $group) {
            if (count($group->getFields()) == 0) {
                Craft::$app->fields->deleteGroup($group);
            }
        }
    }
}
