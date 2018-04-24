<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\console\Application;
use craft\models\UserGroup as UserGroupModel;
use craft\models\Section as SectionModel;
use craft\models\CategoryGroup as CategoryGroupModel;
use craft\base\Volume as VolumeModel;
use Codeception\Test\Unit;

/**
 * Class UserGroupTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserGroupTest extends Unit
{
    /**
     * @var UserGroup
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->converter = new UserGroup();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideUserGroups
     *
     * @param UserGroupModel $group
     * @param array          $definition
     * @param array          $groupPermissions
     * @param array          $allPermissions
     */
    public function testGetRecordDefinition(
        UserGroupModel $group,
        array $definition,
        array $groupPermissions,
        array $allPermissions
    ) {
        Craft::$app->userPermissions->expects($this->exactly(1))
                                    ->method('getPermissionsByGroupId')
                                    ->with($group->id)
                                    ->willReturn($groupPermissions);

        Craft::$app->userPermissions->expects($this->exactly(1))
                                    ->method('getAllPermissions')
                                    ->willReturn($allPermissions);

        Craft::$app->sections->expects($this->exactly(1))
                             ->method('getSectionById')
                             ->with(1)
                             ->willReturn($this->getMockSection(1));

        Craft::$app->categories->expects($this->exactly(1))
                               ->method('getGroupById')
                               ->with(2)
                               ->willReturn($this->getMockCategoryGroup(2));

        Craft::$app->volumes->expects($this->exactly(1))
                            ->method('getVolumeById')
                            ->with(3)
                            ->willReturn($this->getmockVolume(3));

        $result = $this->converter->getRecordDefinition($group);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideUserGroups
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param UserGroupModel $group
     * @param array          $definition
     * @param array          $groupPermissions
     * @param array          $allPermissions
     * @param bool           $valid
     */
    public function testSaveRecord(
        UserGroupModel $group,
        array $definition,
        array $groupPermissions,
        array $allPermissions,
        bool $valid
    ) {
        Craft::$app->userGroups->expects($this->exactly(1))
                               ->method('saveGroup')
                               ->with($group)
                               ->willReturn($valid);

        $mappedPermissions = ['createEntries:1', 'editCategories:2', 'performUpdates', 'viewVolume:3'];

        if ($valid) {
            Craft::$app->userPermissions->expects($this->exactly(1))
                                        ->method('saveGroupPermissions')
                                        ->with($group->id, $mappedPermissions)
                                        ->willReturn(true);

            Craft::$app->sections->expects($this->exactly(1))
                                 ->method('getSectionByHandle')
                                 ->with('section1')
                                 ->willReturn($this->getMockSection(1));

            Craft::$app->categories->expects($this->exactly(1))
                                   ->method('getGroupByHandle')
                                   ->with('group2')
                                   ->willReturn($this->getMockCategoryGroup(2));

            Craft::$app->volumes->expects($this->exactly(1))
                                ->method('getVolumeByHandle')
                                ->with('volume3')
                                ->willReturn($this->getmockVolume(3));
        }

        $result = $this->converter->saveRecord($group, $definition);

        $this->assertSame($valid, $result);
    }

    /**
     * @dataProvider provideUserGroups
     *
     * @param UserGroupModel $group
     */
    public function testDeleteRecord(UserGroupModel $group)
    {
        Craft::$app->userGroups->expects($this->exactly(1))
                               ->method('deleteGroupById')
                               ->with($group->id);

        $this->converter->deleteRecord($group);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideUserGroups()
    {
        $mockUserGroup = $this->getMockUserGroup(1);

        return [
            'valid user group' => [
                'group' => $mockUserGroup,
                'definition' => $this->getMockUserGroupDefinition($mockUserGroup),
                'groupPermissions' => [
                    'createentries:1',
                    'editcategories:2',
                    'performupdates',
                    'viewvolume:3',
                ],
                'allPermissions' => [
                    ['createEntries:1' => []],
                    ['editCategories:2' => []],
                    ['performUpdates' => []],
                    ['viewVolume:3' => []],
                ],
                'validSave' => true,
            ],
            'invalid user group' => [
                'group' => $mockUserGroup,
                'definition' => $this->getMockUserGroupDefinition($mockUserGroup),
                'groupPermissions' => [
                    'createentries:1',
                    'editcategories:2',
                    'performupdates',
                    'viewvolume:3',
                ],
                'allPermissions' => [
                    ['createEntries:1' => []],
                    ['editCategories:2' => []],
                    ['performUpdates' => []],
                    ['viewVolume:3' => []],
                ],
                'validSave' => false,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param UserGroupModel $userGroup
     *
     * @return array
     */
    private function getMockUserGroupDefinition(UserGroupModel $userGroup)
    {
        return [
          'class' => get_class($userGroup),
          'attributes' => [
              'name' => 'userGroupName'.$userGroup->id,
              'handle' => 'userGroupHandle'.$userGroup->id,
          ],
          'permissions' => [
              'createEntries:section1',
              'editCategories:group2',
              'performUpdates',
              'viewVolume:volume3',
          ],
        ];
    }

    /**
     * @param int $userGroupId
     *
     * @return UserGroupModel
     */
    private function getMockUserGroup(int $userGroupId)
    {
        $mockApp = $this->getMockBuilder(Application::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $mockApp->expects($this->exactly(1))
                ->method('requireEdition')
                ->with(Craft::Pro)
                ->willReturn(true);

        Craft::$app = $mockApp;

        $mockUserGroup = $this->getmockBuilder(UserGroupModel::class)
                              ->setMethods(['__toString'])
                              ->getMock();

        $mockUserGroup->id = $userGroupId;
        $mockUserGroup->handle = 'userGroupHandle'.$userGroupId;
        $mockUserGroup->name = 'userGroupName'.$userGroupId;

        return $mockUserGroup;
    }

    /**
     * @param int $sectionId
     *
     * @return Mock|SectionModel
     */
    private function getMockSection(int $sectionId)
    {
        $mockSection = $this->getMockBuilder(SectionModel::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockSection->id = $sectionId;
        $mockSection->handle = 'section'.$sectionId;

        return $mockSection;
    }

    /**
     * @param int $groupId
     *
     * @return Mock|CategoryGroupModel
     */
    private function getMockCategoryGroup(int $groupId)
    {
        $mockGroup = $this->getMockBuilder(CategoryGroupModel::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $mockGroup->id = $groupId;
        $mockGroup->handle = 'group'.$groupId;

        return $mockGroup;
    }

    /**
     * @param int $volumeId
     *
     * @return Mock|VolumeModel
     */
    private function getMockVolume(int $volumeId)
    {
        $mockVolume = $this->getMockBuilder(VolumeModel::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $mockVolume->id = $volumeId;
        $mockVolume->handle = 'volume'.$volumeId;

        return $mockVolume;
    }
}
