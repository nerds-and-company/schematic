<?php

namespace NerdsAndCompany\SchematicTests\Services;

use Craft\BaseTest;
use Craft\UserGroupModel;
use Craft\SectionModel;
use Craft\AssetSourceModel;
use Craft\GlobalSetModel;
use Craft\SectionsService;
use Craft\AssetSourcesService;
use Craft\GlobalsService;
use Craft\UserPermissionsService;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Services\UserGroups;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_PluginsServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass Craft\Schematic_UserGroupsService
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
    public function testSuccessfulExport(array $groups, array $groupPermissions, array $expectedResult = array())
    {
        $this->setMockUserGroupsService();
        $this->setMockUserPermissionsService($groupPermissions);
        $this->setMockSectionsService('id');
        $this->setMockAssetSourcesService('id');
        $this->setMockGlobalsService('id');

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
        $this->setMockSectionsService('handle');
        $this->setMockAssetSourcesService('handle');
        $this->setMockGlobalsService('handle');

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
        $this->setMockSectionsService('handle');
        $this->setMockAssetSourcesService('handle');
        $this->setMockGlobalsService('handle');

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
        $this->setMockSectionsService('handle');
        $this->setMockAssetSourcesService('handle');
        $this->setMockGlobalsService('handle');

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
        return array(
            'emptyArray' => array(
                'userGroups' => array(),
                'groupPermissions' => array(),
                'expectedResult' => array(),
            ),
            'single group without permissions' => array(
                'userGroups' => array(
                    'group1' => $this->getMockUserGroup(1),
                ),
                'groupPermissions' => array(
                    array(1, array()),
                ),
                'expectedResult' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(),
                    ),
                ),
            ),
            'multiple groups without permissions' => array(
                'userGroups' => array(
                    'group1' => $this->getMockUserGroup(1),
                    'group2' => $this->getMockUserGroup(2),
                ),
                'groupPermissions' => array(
                    array(1, array()),
                    array(2, array()),
                ),
                'expectedResult' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(),
                    ),
                    'groupHandle2' => array(
                        'name' => 'groupName2',
                        'permissions' => array(),
                    ),
                ),
            ),
            'single group with permissions' => array(
                'userGroups' => array(
                    'group1' => $this->getMockUserGroup(1),
                ),
                'groupPermissions' => array(
                    array(1, array(
                        'accesssitewhensystemisoff',
                        'performupdates',
                        'editentries:1',
                        'editglobalset:1',
                        'viewassetsource:1',

                    )),
                ),
                'expectedResult' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(
                            'accessSiteWhenSystemIsOff',
                            'editEntries:sectionHandle1',
                            'editGlobalSet:globalSetHandle1',
                            'performUpdates',
                            'viewAssetSource:assetSourceHandle1',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function provideValidUserGroupDefinitions()
    {
        return array(
            'emptyArray' => array(
                'groupDefinitions' => array(),
            ),
            'single group without permissions' => array(
                'groupDefinitions' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(),
                    ),
                ),
            ),
            'single group with permissions' => array(
                'groupDefinitions' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(
                            'accessSiteWhenSystemIsOff',
                            'performUpdates',
                            'editEntries:sectionHandle1',
                            'editGlobalSet:globalSetHandle1',
                            'viewAssetSource:assetSourceHandle1',
                        ),
                    ),
                ),
            ),
        );
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
            ->willReturnMap(array(
                array('id', $groupId),
                array('handle', 'groupHandle'.$groupId),
                array('name', 'groupName'.$groupId),
            ));

        $mockUserGroup->expects($this->any())
            ->method('getAllErrors')
            ->willReturn(array(
                'ohnoes' => 'horrible error',
            ));

        return $mockUserGroup;
    }

    /**
     * @param $indexBy
     *
     * @return Mock|SectionsService
     */
    private function setMockSectionsService($indexBy)
    {
        $mockSectionService = $this->getMockBuilder(SectionsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSectionService->expects($this->any())
            ->method('getAllSections')
            ->with($indexBy)
            ->willReturn($this->getMockSections($indexBy, 2));

        $this->setComponent(craft(), 'sections', $mockSectionService);

        return $mockSectionService;
    }

    /**
     * @param string $indexBy
     * @param int    $count
     *
     * @return Mock[]|SectionModel[]
     */
    private function getMockSections($indexBy, $count)
    {
        $keyPrefix = $indexBy == 'id' ? '' : 'sectionHandle';
        $mockSections = array();
        for ($x = 0; $x <= $count; $x++) {
            $mockSection = $this->getMockBuilder(SectionModel::class)
                ->disableOriginalConstructor()
                ->getMock();

            $mockSection->expects($this->any())
                ->method('__get')
                ->willReturnMap(array(
                    array('handle', 'sectionHandle'.$x),
                    array('id', $x),
                    array('name', 'sectionName'.$x),
                ));

            $mockSections[$keyPrefix.$x] = $mockSection;
        }

        return $mockSections;
    }

    /**
     * @param string $indexBy
     *
     * @return Mock|AssetSourcesService
     */
    private function setMockAssetSourcesService($indexBy)
    {
        $mockAssetSourcesService = $this->getMockBuilder(AssetSourcesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetSourcesService->expects($this->exactly(1))
            ->method('getAllSources')
            ->with($indexBy)
            ->willReturn($this->getMockAssetSources($indexBy, 1));

        $this->setComponent(craft(), 'assetSources', $mockAssetSourcesService);

        return $mockAssetSourcesService;
    }

    /**
     * @param string $indexBy
     * @param int    $count
     *
     * @return Mock[]|AssetSourceModel[]
     */
    private function getMockAssetSources($indexBy, $count)
    {
        $keyPrefix = $indexBy == 'id' ? '' : 'assetSourceHandle';
        $mockAssetSources = array();
        for ($x = 0; $x <= $count; $x++) {
            $mockAssetSource = $this->getMockBuilder(AssetSourceModel::handle)
                ->disableOriginalConstructor()
                ->getMock();

            $mockAssetSource->expects($this->any())
                ->method('__get')
                ->willReturnMap(array(
                    array('handle', 'assetSourceHandle'.$x),
                    array('id', $x),
                    array('name', 'assetSourceName'.$x),
                ));

            $mockAssetSources[$keyPrefix.$x] = $mockAssetSource;
        }

        return $mockAssetSources;
    }

    /**
     * @param string $indexBy
     *
     * @return Mock|AssetSourcesService
     */
    private function setMockGlobalsService($indexBy)
    {
        $mockAssetSourcesService = $this->getMockBuilder(GlobalsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetSourcesService->expects($this->exactly(1))
            ->method('getAllSets')
            ->with($indexBy)
            ->willReturn($this->getMockGlobalSets($indexBy, 1));

        $this->setComponent(craft(), 'globals', $mockAssetSourcesService);

        return $mockAssetSourcesService;
    }

    /**
     * @param string $indexBy
     * @param int    $count
     *
     * @return Mock[]|GlobalSetModel[]
     */
    private function getMockGlobalSets($indexBy, $count)
    {
        $keyPrefix = $indexBy == 'id' ? '' : 'globalSetHandle';
        $mockGlobalSets = array();
        for ($x = 0; $x <= $count; $x++) {
            $mockGlobalSet = $this->getMockBuilder(GlobalSetModel::class)
                ->disableOriginalConstructor()
                ->getMock();

            $mockGlobalSet->expects($this->any())
                ->method('__get')
                ->willReturnMap(array(
                    array('handle', 'globalSetHandle'.$x),
                    array('id', $x),
                    array('name', 'globalSetName'.$x),
                ));

            $mockGlobalSets[$keyPrefix.$x] = $mockGlobalSet;
        }

        return $mockGlobalSets;
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

        $this->setComponent(craft(), 'userGroups', $mockUserGroupsService);

        return $mockUserGroupsService;
    }

    /**
     * @param int $count
     *
     * @return array
     */
    private function getMockUserGroups($count)
    {
        $mockUserGroups = array();
        for ($x = 0; $x <= $count; $x++) {
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
    private function setMockUserPermissionsService(array $permissions = array(), $success = true)
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

        $this->setComponent(craft(), 'userPermissions', $mockUserPermissionsService);

        return $mockUserPermissionsService;
    }

    /**
     * @return array of example permissions
     */
    private function getAllPermissionsExample()
    {
        return array(
            'General' => array(
                'accessSiteWhenSystemIsOff' => array(
                    'nested' => array(
                        'accessCpWhenSystemIsOff' => array(),
                        'performUpdates' => array(),
                        'accessPlugin-PluginName1' => array(),
                        'accessPlugin-PluginName2' => array(),
                    ),
                ),
            ),
            'Users' => array(
                'editUsers' => array(
                    'nested' => array(
                        'registerUsers' => array(),
                        'assignUserPermissions' => array(),
                        'administrateUsers' => array(
                            'nested' => array(
                                'changeUserEmails' => array(),
                            ),
                        ),
                        'deleteUsers' => array(),
                    ),
                ),
            ),
            'Section - 1' => array(
                'editEntries:1' => array(
                    'nested' => array(
                        'publishEntries:1' => array(),
                        'editPeerEntryDrafts:1' => array(
                            'nested' => array(
                                'publishPeerEntryDrafts:1' => array(),
                                'deletePeerEntryDrafts:1' => array(),
                            ),
                        ),
                    ),
                ),
            ),
            'Section - 2' => array(
                'editEntries:2' => array(
                    'nested' => array(
                        'publishEntries:2' => array(),
                        'editPeerEntryDrafts:2' => array(
                            'nested' => array(
                                'publishPeerEntryDrafts:2' => array(),
                                'deletePeerEntryDrafts:2' => array(),
                            ),
                        ),
                    ),
                ),
            ),
            'GlobalSet - 1' => array(
                'editGlobalSet:1' => array(),
            ),
            'AssetSources - 1' => array(
                'viewAssetSource:1' => array(
                    'nested' => array(
                        'uploadToAssetSource:1' => array(),
                        'createSubfoldersInAssetSource:1' => array(),
                        'removeFromAssetSource:1' => array(),
                    ),
                ),
            ),
        );
    }
}
