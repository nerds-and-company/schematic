<?php

namespace NerdsAndCompany\Schematic\Converters\Base;

use Craft;
use craft\base\Model;
use craft\models\FieldGroup;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Fields Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Field extends Base
{
    /**
     * @var number[]
     */
    private $groups;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);

        if ($record->groupId) {
            $definition['group'] = $record->group->name;
        }

        $definition['attributes']['required'] = $definition['attributes']['required'] == true;
        unset($definition['attributes']['context']);
        unset($definition['attributes']['groupId']);
        unset($definition['attributes']['layoutId']);
        unset($definition['attributes']['tabId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition)
    {
        if (array_key_exists('group', $definition)) {
            $record->groupId = $this->getGroupIdByName($definition['group']);
        }

        return Craft::$app->fields->saveField($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record)
    {
        return Craft::$app->fields->deleteField($record);
    }

    /**
     * Get group id by name.
     *
     * @param string $name
     *
     * @return
     */
    private function getGroupIdByName($name)
    {
        if (!isset($this->groups)) {
            $this->groups = [];
            foreach (Craft::$app->fields->getAllGroups() as $group) {
                $this->groups[$group->name] = $group->id;
            }
        }
        if (!array_key_exists($name, $this->groups)) {
            $group = new FieldGroup(['name' => $name]);
            if (Craft::$app->fields->saveGroup($group)) {
                $this->groups[$name] = $group->id;
            } else {
                Craft::$app->schematic_fields->importError($group, $name);
            }
        }

        return $this->groups[$name];
    }
}
