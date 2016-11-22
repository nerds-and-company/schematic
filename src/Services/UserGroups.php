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
 * @copyright Copyright (c) 2015-2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class UserGroups extends Base
{
    /** @var SectionModel[] */
    private $sectionsByHandle = [];
    /** @var SectionModel[] */
    private $sectionsById = [];
    /** @var AssetSourceModel[] */
    private $assetSourceByHandle = [];
    /** @var AssetSourceModel[] */
    private $assetSourceById = [];
    /** @var GlobalSetModel[] */
    private $globalSetsByHandle = [];
    /** @var GlobalSetModel[] */
    private $globalSetsById = [];
    /** @var string[] */
    private $mappedPermissions = [];

    //==============================================================================================================
    //===============================================  SERVICES  ===================================================
    //==============================================================================================================

    /**
     * @return SectionsService
     */
    private function getSectionsService()
    {
        return Craft::app()->sections;
    }

    /**
     * @return AssetSourcesService
     */
    private function getAssetSourcesService()
    {
        return Craft::app()->assetSources;
    }

    /**
     * @return GlobalsService
     */
    private function getGlobalsService()
    {
        return Craft::app()->globals;
    }

    /**
     * @return UserPermissionsService
     */
    private function getUserPermissionsService()
    {
        return Craft::app()->userPermissions;
    }

    /**
     * @return UserGroupsService
     */
    private function getUserGroupsService()
    {
        return Craft::app()->userGroups;
    }

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

        $this->sectionsById = $this->getSectionsService()->getAllSections('id');
        $this->assetSourceById = $this->getAssetSourcesService()->getAllSources('id');
        $this->globalSetsById = $this->getGlobalsService()->getAllSets('id');
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
        $groupPermissions = $this->getUserPermissionsService()->getPermissionsByGroupId($group->id);

        foreach ($groupPermissions as $permission) {
            if (array_key_exists($permission, $this->mappedPermissions)) {
                $permission = $this->mappedPermissions[$permission];
                $permissionDefinitions[] = $this->getPermissionDefinition($permission);
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
        foreach ($this->getUserPermissionsService()->getAllPermissions() as $permissions) {
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

    /**
     * Get permission definition.
     *
     * @param string $permission
     *
     * @return string
     */
    private function getPermissionDefinition($permission)
    {
        if (strpos($permission, 'Asset') > -1) {
            $permission = $this->mapPermissionSource($this->assetSourceById, $permission, true);
        } elseif (strpos($permission, 'GlobalSet') > -1) {
            $permission = $this->mapPermissionSource($this->globalSetsById, $permission, true);
        } else {
            $permission = $this->mapPermissionSource($this->sectionsById, $permission, true);
        }

        return $permission;
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

        $this->sectionsByHandle = $this->getSectionsService()->getAllSections('handle');
        $this->assetSourceByHandle = $this->getAssetSourcesService()->getAllSources('handle');
        $this->globalSetsByHandle = $this->getGlobalsService()->getAllSets('handle');

        $userGroups = $this->getUserGroupsService()->getAllGroups('handle');

        foreach ($groupDefinitions as $groupHandle => $groupDefinition) {
            $group = array_key_exists($groupHandle, $userGroups) ? $userGroups[$groupHandle] : new UserGroupModel();

            unset($userGroups[$groupHandle]);

            $group->name = $groupDefinition['name'];
            $group->handle = $groupHandle;

            if (!$this->getUserGroupsService()->saveGroup($group)) {
                $this->addErrors($group->getAllErrors());

                continue;
            }

            $permissions = $this->getPermissions($groupDefinition['permissions']);

            $this->getUserPermissionsService()->saveGroupPermissions($group->id, $permissions);
        }

        if ($force) {
            foreach ($userGroups as $group) {
                $this->getUserGroupsService()->deleteGroupById($group->id);
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
            $permissions[] = $this->getPermission($permissionDefinition);
        }

        return $permissions;
    }

    /**
     * Get permission.
     *
     * @param string $permissionDefinition
     *
     * @return string
     */
    private function getPermission($permissionDefinition)
    {
        if (strpos($permissionDefinition, 'Asset') > -1) {
            $permissionDefinition = $this->mapPermissionSource($this->assetSourceByHandle, $permissionDefinition, false);
        } elseif (strpos($permissionDefinition, 'GlobalSet') > -1) {
            $permissionDefinition = $this->mapPermissionSource($this->globalSetsByHandle, $permissionDefinition, false);
        } else {
            $permissionDefinition = $this->mapPermissionSource($this->sectionsByHandle, $permissionDefinition, false);
        }

        return $permissionDefinition;
    }

    //==============================================================================================================
    //===============================================  HELPERS  ====================================================
    //==============================================================================================================

    /**
     * @param BaseElementModel[] $mapping    AssetSources or Sections
     * @param string             $permission
     * @param bool               $export     is it an export or import
     *
     * @return string mapped permission
     */
    private function mapPermissionSource(array $mapping, $permission, $export)
    {
        if (strpos($permission, ':') > -1) {
            /** @var BaseElementModel $source */
            $source = false;
            list($permissionName, $sourceId) = explode(':', $permission);

            if (isset($mapping[$sourceId])) {
                $source = $mapping[$sourceId];
            }

            if ($source) {
                $permission = $permissionName.':'.($export ? $source->handle : $source->id);
            }
        }

        return $permission;
    }
}
