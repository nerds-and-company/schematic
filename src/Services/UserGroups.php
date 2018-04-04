<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Model;
use craft\models\UserGroup;

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
class UserGroups extends Base
{
    protected $recordClass = UserGroup::class;

    /** @var string[] */
    private $mappedPermissions = [];

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all section records
     *
     * @return UserGroup[]
     */
    protected function getRecords()
    {
        $this->mappedPermissions = $this->getAllMappedPermissions();

        return Craft::$app->userGroups->getAllGroups();
    }

    /**
     * Get group definition.
     *
     * @param UserGroupModel $group
     *
     * @return array
     */
    protected function getRecordDefinition(Model $record)
    {
        $attributes = parent::getRecordDefinition($record);
        $attributes['permissions'] = $this->getGroupPermissionDefinitions($record);
        return $attributes;
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
        $groupPermissions = Craft::$app->userPermissions->getPermissionsByGroupId($group->id);

        foreach ($groupPermissions as $permission) {
            if (array_key_exists($permission, $this->mappedPermissions)) {
                $permission = $this->mappedPermissions[$permission];
                $permissionDefinitions[] = $this->getSource(false, $permission, 'id', 'handle');
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
        foreach (Craft::$app->userPermissions->getAllPermissions() as $permissions) {
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
     * Save a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function saveRecord(Model $record)
    {
        return Craft::$app->userGroups->saveGroup($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->userGroups->deleteGroup($record);
    }
}
