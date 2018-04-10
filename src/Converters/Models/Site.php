<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\SiteGroup;
use craft\base\Model;

/**
 * Schematic Globals Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Site extends Base
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

        $definition['group'] = $record->group->name;
        unset($definition['attributes']['groupId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition)
    {
        if ($definition['group']) {
            $record->groupId = $this->getGroupIdByName($definition['group']);
        }

        return Craft::$app->sites->saveSite($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record)
    {
        return Craft::$app->sites->deleteSiteById($record->id);
    }

    /**
     * Get group id by name.
     *
     * @param string $name
     *
     * @return
     */
    public function getGroupIdByName($name)
    {
        if (!isset($this->groups)) {
            $this->groups = [];
            foreach (Craft::$app->sites->getAllGroups() as $group) {
                $this->groups[$group->name] = $group->id;
            }
        }
        if (!array_key_exists($name, $this->groups)) {
            $group = new SiteGroup(['name' => $name]);
            if (Craft::$app->sites->saveGroup($group)) {
                $this->groups[$name] = $group->id;
            } else {
                return $this->importError($group, $name);
            }
        }

        return $this->groups[$name];
    }
}
