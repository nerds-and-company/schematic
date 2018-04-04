<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\fields\PlainText;
use craft\models\FieldGroup;

/**
 * Schematic Fields Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Fields extends Base
{
    /**
     * @var number[]
     */
    private $groups;

    /**
     * Get all field groups
     *
     * @return FieldGroup[]
     */
    protected function getRecords()
    {
        return Craft::$app->fields->getAllFields();
    }

    /**
     * Get section definition.
     *
     * @param Model $record
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        if ($record instanceof Field) {
            $definition['group'] = $record->group->name;
            unset($definition['attributes']['groupId']);
            unset($definition['attributes']['layoutId']);
            unset($definition['attributes']['tabId']);
        }

        return $definition;
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @param array $definition
     * @return boolean
     */
    protected function saveRecord(Model $record, array $definition)
    {
        $record->setAttributes($definition['attributes']);
        $record->groupId = $this->getGroupIdByName($definition['group']);
        return Craft::$app->fields->saveField($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->fields->deleteField($record);
    }

    /**
     * Get group id by name
     * @param  string $name
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
                $this->importError($group, $name);
            }
        }
        return $this->groups[$name];
    }
}
