<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class Schematic_PluginsServiceTest
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\Schematic_UserGroupsService
 * @covers ::__construct
 * @covers ::<!public>
 */
class Schematic_UserGroupsServiceTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../SchematicPlugin.php';
        require_once __DIR__.'/../models/Schematic_ResultModel.php';
        require_once __DIR__.'/../services/Schematic_AbstractService.php';
        require_once __DIR__.'/../services/Schematic_UserGroupsService.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidUserGroups
     *
     * @param UserGroupModel[] $groups
     * @param array $expectedResult
     */
    public function testExportDefault(array $groups, array $expectedResult = array())
    {
        $this->setMockUserGroupsService();
        $this->setMockUserPermissionsService();
        $this->setMockSectionsService('id');
        $this->setMockAssetSourcesService('id');

        $schematicUserGroupsService = new Schematic_UserGroupsService();

        $actualResult = $schematicUserGroupsService->export($groups);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     *
     * @param array $groupDefinitions
     */
    public function testImportDefault(array $groupDefinitions = array())
    {
        $this->setMockSectionsService('handle');
        $this->setMockAssetSourcesService('handle');

        $schematicUserGroupsService = new Schematic_UserGroupsService();

        $import = $schematicUserGroupsService->import($groupDefinitions);

        $this->assertTrue($import instanceof Schematic_ResultModel);
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
                'expectedResult' => array(),
            ),
            'single group without permissions' => array(
                'userGroups' => array(
                    'group1' => $this->getMockUserGroup('groupHandle', 'groupName', array()),
                ),
                'expectedResult' => array(
                    'groupHandle' => array(
                        'name' => 'groupName',
                        'permissions' => array(),
                    )
                )
            ),
            'multiple groups without permissions' => array(
                'userGroups' => array(
                    'group1' => $this->getMockUserGroup('groupHandle1', 'groupName1', array()),
                    'group2' => $this->getMockUserGroup('groupHandle2', 'groupName2', array()),
                ),
                'expectedResult' => array(
                    'groupHandle1' => array(
                        'name' => 'groupName1',
                        'permissions' => array(),
                    ),
                    'groupHandle2' => array(
                        'name' => 'groupName2',
                        'permissions' => array(),
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    public function provideValidUserGroupDefinitions()
    {
        return array();
    }

    /**
     * @return array
     */
    public function provideInvalidUserGroupDefinitions()
    {
        return array();
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $handle
     * @param string $name
     * @param array $permissions
     * @return MockObject|UserGroupModel
     */
    private function getMockUserGroup($handle, $name, array $permissions = array())
    {
        $mockUserGroup = $this->getMockBuilder('Craft\UserGroupModel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserGroup->expects($this->any())
            ->method('__get')
            ->willReturnMap(array(
                array('handle', $handle),
                array('name', $name),
            ));

        return $mockUserGroup;
    }

    /**
     * @param $indexBy
     * @param array $sections
     * @return MockObject|SectionsService
     */
    private function setMockSectionsService($indexBy, array $sections = array())
    {
        $mockSectionService = $this->getMockBuilder('Craft\SectionsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSectionService->expects($this->exactly(1))
            ->method('getAllSections')
            ->with($indexBy)
            ->willReturn($sections);

        $this->setComponent(craft(), 'sections', $mockSectionService);

        return $mockSectionService;
    }

    /**
     * @param string $indexBy
     * @param AssetSourceModel[] $assetSources
     * @return MockObject|AssetSourcesService
     */
    private function setMockAssetSourcesService($indexBy, array $assetSources = array())
    {
        $mockAssetSourcesService = $this->getMockBuilder('Craft\AssetSourcesService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetSourcesService->expects($this->exactly(1))
            ->method('getAllSources')
            ->with($indexBy)
            ->willReturn($assetSources);

        $this->setComponent(craft(), 'assetSources', $mockAssetSourcesService);

        return $mockAssetSourcesService;
    }

    /**
     * @return MockObject|UserGroupsService
     */
    private function setMockUserGroupsService()
    {
        $mockUserGroupsService = $this->getMockBuilder('Craft\UserGroupsService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setComponent(craft(), 'userGroups', $mockUserGroupsService);

        return $mockUserGroupsService;
    }

    /**
     * @param array $permissions
     * @return MockObject|UserPermissionsService
     */
    private function setMockUserPermissionsService(array $permissions = array())
    {
        $mockUserPermissionsService = $this->getMockBuilder('Craft\UserPermissionsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserPermissionsService->expects($this->any())
            ->method('getAllPermissions')
            ->willReturn($permissions);

        $mockUserPermissionsService->expects($this->any())
            ->method('getPermissionsByGroupId')
            ->willReturn(array());

        $this->setComponent(craft(), 'userPermissions', $mockUserPermissionsService);

        return $mockUserPermissionsService;
    }
}
