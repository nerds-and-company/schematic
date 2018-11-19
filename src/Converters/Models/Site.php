<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\SiteGroup;
use craft\base\Model;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Sites Converter.
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
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record->groupId) {
            $definition['group'] = $record->group->name;
        }
        unset($definition['attributes']['groupId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        if ($definition['group']) {
            $record->groupId = $this->getGroupIdByName($definition['group']);
        }

        return Craft::$app->sites->saveSite($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->sites->deleteSiteById($record->id);
    }

    /**
     * Get group id by name.
     *
     * @param string $name
     *
     * @return int|null
     */
    public function getGroupIdByName($name)
    {
        if (!isset($this->groups)) {
            $this->resetCraftSitesServiceGroupsCache();

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
                Schematic::importError($group, $name);

                return null;
            }
        }

        return $this->groups[$name];
    }

    /**
     * Reset craft site service groups cache using reflection.
     */
    private function resetCraftSitesServiceGroupsCache()
    {
        $obj = Craft::$app->sites;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllGroups')) {
            $refProperty = $refObject->getProperty('_fetchedAllGroups');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
    }
}
