<?php

namespace Craft;

/**
 * Schematic User Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_UserGroupsService extends Schematic_AbstractService
{
    /** @var SectionModel[] */
    private $sectionsByHandle = array();
    /** @var SectionModel[] */
    private $sectionsById = array();
    /** @var AssetSourceModel[] */
    private $assetSourceByHandle = array();
    /** @var AssetSourceModel[] */
    private $assetSourceById = array();
    /** @var GlobalSetModel[] */
    private $globalSetsByHandle = array();
    /** @var GlobalSetModel[] */
    private $globalSetsById = array();
    /** @var string[] */
    private $mappedPermissions = array();

    //==============================================================================================================
    //===============================================  SERVICES  ===================================================
    //==============================================================================================================

    /**
     * @return SectionsService
     */
    private function getSectionsService()
    {
        return craft()->sections;
    }

    /**
     * @return AssetSourcesService
     */
    private function getAssetSourcesService()
    {
        return craft()->assetSources;
    }

    /**
     * @return GlobalsService
     */
    private function getGlobalsService()
    {
        return craft()->globals;
    }

    /**
     * @return UserPermissionsService
     */
    private function getUserPermissionsService()
    {
        return craft()->userPermissions;
    }

    /**
     * @return UserGroupsService
     */
    private function getUserGroupsService()
    {
        return craft()->userGroups;
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
    public function export(array $groups = array())
    {
        Craft::log(Craft::t('Exporting User Groups'));

        $groupDefinitions = array();

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
        return array(
            'name' => $group->name,
            'permissions' => $this->getGroupPermissionDefinitions($group),
        );
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
        $permissionDefinitions = array();
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
        $mappedPermissions = array();
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
        $mappedPermissions = array();
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
     * @return Schematic_ResultModel
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
        $permissions = array();
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
