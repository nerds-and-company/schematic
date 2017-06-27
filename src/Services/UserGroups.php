<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\UserGroupModel;

/**
 * Schematic User Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserGroups extends Base
{
    /** @var string[] */
    private $mappedPermissions = [];

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Export user groups.
     *
     * @param UserGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups = [])
    {
        Craft::log(Craft::t('Exporting User Groups'));

        $groupDefinitions = [];

        $this->mappedPermissions = $this->getAllMappedPermissions();

        foreach ($groups as $group) {
            $groupDefinitions[$group->handle] = $this->getGroupDefinition($group);
        }

        return $groupDefinitions;
    }

    /**
     * Get group definition.
     *
     * @param UserGroupModel $group
     *
     * @return array
     */
    private function getGroupDefinition(UserGroupModel $group)
    {
        return [
            'name' => $group->name,
            'permissions' => $this->getGroupPermissionDefinitions($group),
        ];
    }

    /**
     * Get group permissions.
     *
     * @param $group
     *
     * @return array|string
     */
    private function getGroupPermissionDefinitions($group)
    {
        $permissionDefinitions = [];
        $groupPermissions = Craft::app()->userPermissions->getPermissionsByGroupId($group->id);

        foreach ($groupPermissions as $permission) {
            if (array_key_exists($permission, $this->mappedPermissions)) {
                $permission = $this->mappedPermissions[$permission];
                $permissionDefinitions[] = Craft::app()->schematic_sources->getSource(false, $permission, 'id', 'handle');
            }
        }
        sort($permissionDefinitions);

        return $permissionDefinitions;
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
        foreach (Craft::app()->userPermissions->getAllPermissions() as $permissions) {
            $mappedPermissions = array_merge($mappedPermissions, $this->getMappedPermissions($permissions));
        }

        return $mappedPermissions;
    }

    /**
     * @param array $permissions
     *
     * @return array
     */
    private function getMappedPermissions(array $permissions)
    {
        $mappedPermissions = [];
        foreach ($permissions as $permission => $options) {
            $mappedPermissions[strtolower($permission)] = $permission;
            if (array_key_exists('nested', $options)) {
                $mappedPermissions = array_merge($mappedPermissions, $this->getMappedPermissions($options['nested']));
            }
        }

        return $mappedPermissions;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Import usergroups.
     *
     * @param array $groupDefinitions
     * @param bool  $force            if set to true items not in the import will be deleted
     *
     * @return Result
     */
    public function import(array $groupDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing User Groups'));

        $userGroups = Craft::app()->userGroups->getAllGroups('handle');

        foreach ($groupDefinitions as $groupHandle => $groupDefinition) {
            $group = array_key_exists($groupHandle, $userGroups) ? $userGroups[$groupHandle] : new UserGroupModel();

            unset($userGroups[$groupHandle]);

            $group->name = $groupDefinition['name'];
            $group->handle = $groupHandle;

            if (!Craft::app()->userGroups->saveGroup($group)) {
                $this->addErrors($group->getAllErrors());

                continue;
            }

            $permissions = $this->getPermissions($groupDefinition['permissions']);

            Craft::app()->userPermissions->saveGroupPermissions($group->id, $permissions);
        }

        if ($force) {
            foreach ($userGroups as $group) {
                Craft::app()->userGroups->deleteGroupById($group->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Get permissions.
     *
     * @param array $permissionDefinitions
     *
     * @return array
     */
    private function getPermissions(array $permissionDefinitions)
    {
        $permissions = [];
        foreach ($permissionDefinitions as $permissionDefinition) {
            $permissions[] = Craft::app()->schematic_sources->getSource(false, $permissionDefinition, 'handle', 'id');
        }

        return $permissions;
    }
}
