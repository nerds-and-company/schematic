<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;

/**
 * Schematic User Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserGroup extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $attributes = parent::getRecordDefinition($record);
        $mappedPermissions = $this->getAllMappedPermissions();

        $groupPermissions = [];
        foreach (Craft::$app->userPermissions->getPermissionsByGroupId($record->id) as $permission) {
            if (array_key_exists($permission, $mappedPermissions)) {
                $groupPermissions[] = $mappedPermissions[$permission];
            } else {
                $groupPermissions[] = $permission;
            }
        }

        $permissionDefinitions = $this->getSources(false, $groupPermissions, 'id', 'handle');
        sort($permissionDefinitions);

        $attributes['permissions'] = $permissionDefinitions;

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        if (Craft::$app->userGroups->saveGroup($record)) {
            $permissions = $this->getSources(false, $definition['permissions'], 'handle', 'id');

            return Craft::$app->userPermissions->saveGroupPermissions($record->id, $permissions);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        return Craft::$app->userGroups->deleteGroup($record);
    }

    /**
     * Get a mapping of all permissions from lowercase to camelcase
     * savePermissions only accepts camelcase.
     *
     * @return array
     */
    private function getAllMappedPermissions()
    {
        $mappedPermissions = [];
        foreach (Craft::$app->userPermissions->getAllPermissions() as $permissions) {
            $mappedPermissions = array_merge($mappedPermissions, $this->getMappedPermissions($permissions));
        }

        return $mappedPermissions;
    }

    /**
     * Recursive function to get mapped permissions.
     *
     * @param array $permissions
     *
     * @return array
     */
    private function getMappedPermissions(array $permissions)
    {
        $mappedPermissions = [];
        foreach ($permissions as $permission => $options) {
            $mappedPermissions[strtolower($permission)] = $permission;
            if (is_array($options) && array_key_exists('nested', $options)) {
                $mappedPermissions = array_merge($mappedPermissions, $this->getMappedPermissions($options['nested']));
            }
        }

        return $mappedPermissions;
    }
}
