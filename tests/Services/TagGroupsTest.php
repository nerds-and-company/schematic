<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\TagsService;
use Craft\TagGroupModel;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class TagGroupsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\TagGroups
 * @covers ::__construct
 * @covers ::<!public>
 */
class TagGroupsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidTagGroups
     *
     * @param TagGroupModel[] $groups
     * @param array           $expectedResult
     */
    public function testSuccessfulExport(array $groups, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicTagGroupsService = new TagGroups();

        $actualResult = $schematicTagGroupsService->export($groups);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTagGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testSuccessfulImport(array $groupDefinitions)
    {
        $this->setMockTagsService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicUserGroupsService = new TagGroups();

        $import = $schematicUserGroupsService->import($groupDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTagGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWithForceOption(array $groupDefinitions)
    {
        $this->setMockTagsService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicUserGroupsService = new TagGroups();

        $import = $schematicUserGroupsService->import($groupDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidTagGroups()
    {
        return [
            'emptyArray' => [
                'TagGroups' => [],
                'expectedResult' => [],
            ],
            'single group' => [
                'TagGroups' => [
                    'group1' => $this->getMockTagGroup(1),
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
            'multiple groups' => [
                'TagGroups' => [
                    'group1' => $this->getMockTagGroup(1),
                    'group2' => $this->getMockTagGroup(2),
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                    'groupHandle2' => [
                        'name' => 'groupName2',
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidTagGroupDefinitions()
    {
        return [
            'emptyArray' => [
                'groupDefinitions' => [],
            ],
            'single group' => [
                'groupDefinitions' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'fieldLayout' => [
                            'fields' => [],
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
     * @return Mock|TagGroupModel
     */
    private function getMockTagGroup($groupId)
    {
        $mockTagGroup = $this->getMockBuilder(TagGroupModel::class)
            ->setMethods(['__get', 'getAllErrors', 'getFieldLayout'])
            //->disableOriginalConstructor()
            ->getMock();

        $mockTagGroup->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $groupId],
                ['fieldLayoutId', $groupId],
                ['handle', 'groupHandle'.$groupId],
                ['name', 'groupName'.$groupId],
            ]);

        $mockTagGroup->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        $mockTagGroup->expects($this->any())
            ->method('getFieldLayout')
            ->willReturn($this->getMockFieldLayout());

        return $mockTagGroup;
    }

    /**
     * @return Mock|CraftFieldsService
     */
    private function setMockFieldsService()
    {
        $mockFieldsService = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFieldsService->expects($this->any())
            ->method('getLayoutById')
            ->with($this->isType('integer'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);

        return $mockFieldsService;
    }

    /**
     * @return Mock|fields
     */
    private function setMockSchematicFields()
    {
        $mockSchematicFields = $this->getMockBuilder(Fields::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayoutDefinition')
            ->with($this->isInstanceOf(FieldLayoutModel::class))
            ->willReturn(['fields' => []]);

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayout')
            ->with($this->isType('array'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFields);

        return $mockSchematicFields;
    }

    /**
     * @return Mock|TagsService
     */
    private function setMockTagsService()
    {
        $mockTagsService = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTagGroups', 'saveTagGroup', 'deleteTagGroupById'])
            ->getMock();

        $mockTagsService->expects($this->any())
            ->method('getAllTagGroups')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'tags', $mockTagsService);

        return $mockTagsService;
    }

    /**
     * @return Mock|FieldLayoutModel
     */
    private function getMockFieldLayout()
    {
        $mockFieldLayout = $this->getMockBuilder(FieldLayoutModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mockFieldLayout;
    }

    /**
     * @return Mock|DbConnection
     */
    private function setMockDbConnection()
    {
        $mockDbConnection = $this->getMockBuilder(DbConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommand'])
            ->getMock();
        $mockDbConnection->autoConnect = false; // Do not auto connect

        $mockDbCommand = $this->getMockDbCommand();
        $mockDbConnection->expects($this->any())->method('createCommand')->willReturn($mockDbCommand);

        Craft::app()->setComponent('db', $mockDbConnection);

        return $mockDbConnection;
    }

    /**
     * @return Mock|DbCommand
     */
    private function getMockDbCommand()
    {
        $mockDbCommand = $this->getMockBuilder(DbCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['insertOrUpdate'])
            ->getMock();

        return $mockDbCommand;
    }
}
