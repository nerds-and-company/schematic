<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\Site;
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
class Sites extends Base
{
    /**
     * @var number[]
     */
    private $groups;

    /**
     * Get all sites.
     *
     * @return Site[]
     */
    protected function getRecords()
    {
        return Craft::$app->sites->getAllSites();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordDefinition(Model $record)
    {
        $definition = parent::getRecordDefinition($record);
        if ($record instanceof Site) {
            $definition['group'] = $record->group->name;
            unset($definition['attributes']['groupId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveRecord(Model $record, array $definition)
    {
        if ($definition['group']) {
            $record->groupId = $this->getGroupIdByName($definition['group']);
        }

        return Craft::$app->sites->saveSite($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteRecord(Model $record)
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
    private function getGroupIdByName($name)
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
