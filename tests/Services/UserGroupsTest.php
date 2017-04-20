<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\UserGroupModel;
use Craft\UserGroupsService;
use Craft\UserPermissionsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class UserGroupsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\UserGroups
 * @covers ::__construct
 * @covers ::<!public>
 */
class UserGroupsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidUserGroups
     *
     * @param UserGroupModel[] $groups
     * @param string[]         $groupPermissions
     * @param array            $expectedResult
     */
    public function testSuccessfulExport(array $groups, array $groupPermissions, array $expectedResult = [])
    {
        $this->setMockUserGroupsService();
        $this->setMockUserPermissionsService($groupPermissions);
        $this->setMockSources();

        $schematicUserGroupsService = new UserGroups();

        $actualResult = $schematicUserGroupsService->export($groups);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidUserGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testSuccessfulImport(array $groupDefinitions)
    {
        $this->setMockUserGroupsService();
        $this->setMockUserPermissionsService();
        $this->setMockSources();

        $schematicUserGroupsService = new UserGroups();

        $import = $schematicUserGroupsService->import($groupDefinitions);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidUserGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWhereSavingGroupFails(array $groupDefinitions)
    {
        $this->setMockUserGroupsService(false);
        $this->setMockUserPermissionsService();
        $this->setMockSources();

        $schematicUserGroupsService = new UserGroups();
        $import = $schematicUserGroupsService->import($groupDefinitions);

        $this->assertTrue($import instanceof Result);
        if (!empty($groupDefinitions)) {
            $this->assertTrue($import->hasErrors());
        }
    }

    /**
     * @covers ::import
     * @dataProvider provideValidUserGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWithForceOption(array $groupDefinitions)
    {
        $this->setMockUserGroupsService();
        $this->setMockUserPermissionsService();
        $this->setMockSources();

        $schematicUserGroupsService = new UserGroups();

        $import = $schematicUserGroupsService->import($groupDefinitions, true);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidUserGroups()
    {
        return [
            'emptyArray' => [
                'userGroups' => [],
                'groupPermissions' => [],
                'expectedResult' => [],
            ],
            'single group without permissions' => [
                'userGroups' => [
                    'group1' => $this->getMockUserGroup(1),
                ],
                'groupPermissions' => [
                    [1, []],
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'permissions' => [],
                    ],
                ],
            ],
            'multiple groups without permissions' => [
                'userGroups' => [
                    'group1' => $this->getMockUserGroup(1),
                    'group2' => $this->getMockUserGroup(2),
                ],
                'groupPermissions' => [
                    [1, []],
                    [2, []],
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'permissions' => [],
                    ],
                    'groupHandle2' => [
                        'name' => 'groupName2',
                        'permissions' => [],
                    ],
                ],
            ],
            'single group with permissions' => [
                'userGroups' => [
                    'group1' => $this->getMockUserGroup(1),
                ],
                'groupPermissions' => [
                    [1, [
                        'accesssitewhensystemisoff',
                        'performupdates',
                        'editentries:1',
                        'editglobalset:1',
                        'viewassetsource:1',

                    ]],
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'permissions' => [
                            'accessSiteWhenSystemIsOff',
                            'editEntries:sectionHandle1',
                            'editGlobalSet:globalSetHandle1',
                            'performUpdates',
                            'viewAssetSource:assetSourceHandle1',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidUserGroupDefinitions()
    {
        return [
            'emptyArray' => [
                'groupDefinitions' => [],
            ],
            'single group without permissions' => [
                'groupDefinitions' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'permissions' => [],
                    ],
                ],
            ],
            'single group with permissions' => [
                'groupDefinitions' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'permissions' => [
                            'accessSiteWhenSystemIsOff',
                            'performUpdates',
                            'editEntries:sectionHandle1',
                            'editGlobalSet:globalSetHandle1',
                            'viewAssetSource:assetSourceHandle1',
                        ],
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $groupId
     *
     * @return Mock|UserGroupModel
     */
    private function getMockUserGroup($groupId)
    {
        $mockUserGroup = $this->getMockBuilder(UserGroupModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserGroup->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $groupId],
                ['handle', 'groupHandle'.$groupId],
                ['name', 'groupName'.$groupId],
            ]);

        $mockUserGroup->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockUserGroup;
    }

    /**
     * @param bool $success
     *
     * @return UserGroupsService|Mock
     */
    private function setMockUserGroupsService($success = true)
    {
        $mockUserGroupsService = $this->getMockBuilder(UserGroupsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserGroupsService->expects($this->any())
            ->method('getAllGroups')
            ->with('handle')
            ->willReturn($this->getMockuserGroups(2));

        $mockUserGroupsService->expects($this->any())
            ->method('saveGroup')
            ->with($this->isInstanceOf(UserGroupModel::class))
            ->willReturn($success);

        $this->setComponent(Craft::app(), 'userGroups', $mockUserGroupsService);

        return $mockUserGroupsService;
    }

    /**
     * @param int $count
     *
     * @return array
     */
    private function getMockUserGroups($count)
    {
        $mockUserGroups = [];
        for ($x = 0; $x <= $count; ++$x) {
            $mockUserGroups['groupHandle'.$x] = $this->getMockUserGroup($x);
        }

        return $mockUserGroups;
    }

    /**
     * @param array $permissions
     * @param bool  $success
     *
     * @return UserPermissionsService|Mock
     */
    private function setMockUserPermissionsService(array $permissions = [], $success = true)
    {
        $mockUserPermissionsService = $this->getMockBuilder(UserPermissionsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserPermissionsService->expects($this->any())
            ->method('getAllPermissions')
            ->willReturn($this->getAllPermissionsExample());

        $mockUserPermissionsService->expects($this->any())
            ->method('getPermissionsByGroupId')
            ->willReturnMap($permissions);

        $mockUserPermissionsService->expects($this->any())
            ->method('saveGroupPermissions')
            ->willReturn($success);

        $this->setComponent(Craft::app(), 'userPermissions', $mockUserPermissionsService);

        return $mockUserPermissionsService;
    }

    /**
     * @return Mock|Sources
     */
    private function setMockSources()
    {
        $mockSources = $this->getMockBuilder(Sources::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSources->expects($this->any())
            ->method('getSource')
            ->will($this->returnCallback(array($this, 'getMockSourceCallback')));

        $this->setComponent(Craft::app(), 'schematic_sources', $mockSources);

        return $mockSources;
    }

    /**
     * @param string $fieldType
     * @param string $source
     * @param string $fromIndex
     * @param string $toIndex
     *
     * @return string
     */
    public function getMockSourceCallback($fieldType, $source, $fromIndex, $toIndex)
    {
        switch ($source) {
            case 'editEntries:sectionHandle1':
                return 'editEntries:1';
            case 'editEntries:1':
                return 'editEntries:sectionHandle1';
            case 'editGlobalSet:globalSetHandle1':
                return 'editGlobalSet:1';
            case 'editGlobalSet:1':
                return 'editGlobalSet:globalSetHandle1';
            case 'viewAssetSource:assetSourceHandle1':
                return 'viewAssetSource:1';
            case 'viewAssetSource:1':
                return 'viewAssetSource:assetSourceHandle1';
            default:
                return $source;
        }
    }

    /**
     * @return array of example permissions
     */
    private function getAllPermissionsExample()
    {
        return [
            'General' => [
                'accessSiteWhenSystemIsOff' => [
                    'nested' => [
                        'accessCpWhenSystemIsOff' => [],
                        'performUpdates' => [],
                        'accessPlugin-PluginName1' => [],
                        'accessPlugin-PluginName2' => [],
                    ],
                ],
            ],
            'Users' => [
                'editUsers' => [
                    'nested' => [
                        'registerUsers' => [],
                        'assignUserPermissions' => [],
                        'administrateUsers' => [
                            'nested' => [
                                'changeUserEmails' => [],
                            ],
                        ],
                        'deleteUsers' => [],
                    ],
                ],
            ],
            'Section - 1' => [
                'editEntries:1' => [
                    'nested' => [
                        'publishEntries:1' => [],
                        'editPeerEntryDrafts:1' => [
                            'nested' => [
                                'publishPeerEntryDrafts:1' => [],
                                'deletePeerEntryDrafts:1' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'Section - 2' => [
                'editEntries:2' => [
                    'nested' => [
                        'publishEntries:2' => [],
                        'editPeerEntryDrafts:2' => [
                            'nested' => [
                                'publishPeerEntryDrafts:2' => [],
                                'deletePeerEntryDrafts:2' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'GlobalSet - 1' => [
                'editGlobalSet:1' => [],
            ],
            'AssetSources - 1' => [
                'viewAssetSource:1' => [
                    'nested' => [
                        'uploadToAssetSource:1' => [],
                        'createSubfoldersInAssetSource:1' => [],
                        'removeFromAssetSource:1' => [],
                    ],
                ],
            ],
        ];
    }
}
