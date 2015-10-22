<?php

namespace Craft;

/**
 * Schematic User Groups Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
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

    /**
     * Set the sections fields.
     */
    public function __construct()
    {
        parent::__construct();
        $this->sectionsByHandle = craft()->sections->getAllSections('handle');
        $this->sectionsById = craft()->sections->getAllSections('id');
        $this->assetSourceByHandle = craft()->assetSources->getAllSources('handle');
        $this->assetSourceById = craft()->assetSources->getAllSources('id');
    }

    /**
     * Export user groups.
     *
     * @param UserGroupModel[] $groups
     *
     * @return array
     */
    public function export(array $groups = array())
    {
        $groupDefinitions = array();

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
        $permissionDefinitions = array();

        foreach (craft()->userPermissions->getAllPermissions() as $label => $permissions) {
            $permissionDefinitions = array_merge($permissionDefinitions, $this->getGroupPermissions($group, $permissions));
        }

        return array(
            'name' => $group->name,
            'permissions' => $permissionDefinitions,
        );
    }

    /**
     * Get group permissions.
     *
     * @param $group
     * @param $permissions
     *
     * @return array|string
     */
    private function getGroupPermissions($group, $permissions)
    {
        $permissionDefinitions = array();
        foreach ($permissions as $permission => $options) {
            if (craft()->userPermissions->doesGroupHavePermission($group->id, $permission)) {
                $permissionDefinitions[] = $this->getPermissionDefinition($permission);
                if (array_key_exists('nested', $options)) {
                    $permissionDefinitions = array_merge($permissionDefinitions, $this->getGroupPermissions($group, $options['nested']));
                }
            }
        }

        return $permissionDefinitions;
    }

    /**
     * Import usergroups.
     *
     * @param array $groupDefinitions
     * @param bool $force if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import(array $groupDefinitions, $force = false)
    {
        $userGroups = craft()->userGroups->getAllGroups('handle');

        foreach ($groupDefinitions as $groupHandle => $groupDefinition) {
            $group = array_key_exists($groupHandle, $userGroups) ? $userGroups[$groupHandle] : new UserGroupModel();

            unset($userGroups[$groupHandle]);

            $group->name = $groupDefinition['name'];
            $group->handle = $groupHandle;

            if (!craft()->userGroups->saveGroup($group)) {
                $this->addErrors($group->getAllErrors());

                continue;
            }

            $permissions = $this->getPermissions($groupDefinition['permissions']);

            craft()->userPermissions->saveGroupPermissions($group->id, $permissions);
        }

        if ($force) {
            foreach ($userGroups as $group) {
                craft()->userGroups->deleteGroupById($group->id);
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
     * Get permission definition.
     *
     * @param string $permission
     *
     * @return string
     */
    private function getPermissionDefinition($permission)
    {
        if (strpos($permission, ':') > -1) {
            $source = false;
            $permissionArray = explode(':', $permission);

            if (strpos($permission, 'Asset') > -1) {
                $source = $this->assetSourceById[$permissionArray[1]];
            } elseif (isset($this->sectionsById[$permissionArray[1]])) {
                $source = $this->sectionsById[$permissionArray[1]];
            }

            if ($source) {
                $permission = $permissionArray[0] . ':' . $source->handle;
            }
        }
        return $permission;
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
        if (strpos($permissionDefinition, ':') > -1) {
            $source = false;
            $permissionArray = explode(':', $permissionDefinition);

            if (strpos($permissionDefinition, 'Asset') > -1) {
                $source = $this->assetSourceByHandle[$permissionArray[1]];
            } elseif (isset($this->sectionsByHandle[$permissionArray[1]])) {
                $source = $this->sectionsByHandle[$permissionArray[1]];
            }

            if ($source) {
                $permissionDefinition = $permissionArray[0] . ':' . $source->id;
            }
        }

        return $permissionDefinition;
    }
}
