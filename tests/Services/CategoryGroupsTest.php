<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\CategoriesService;
use Craft\CategoryGroupLocaleModel;
use Craft\CategoryGroupModel;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class CategoryGroupsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\CategoryGroups
 * @covers ::__construct
 * @covers ::<!public>
 */
class CategoryGroupsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidCategoryGroups
     *
     * @param CategoryGroupModel[] $groups
     * @param array                $expectedResult
     */
    public function testSuccessfulExport(array $groups, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicCategoryGroupsService = new CategoryGroups();

        $actualResult = $schematicCategoryGroupsService->export($groups);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testSuccessfulImport(array $groupDefinitions)
    {
        $this->setMockCategoriesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicCategoryGroupsService = new CategoryGroups();

        $import = $schematicCategoryGroupsService->import($groupDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWithForceOption(array $groupDefinitions)
    {
        $this->setMockCategoriesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicCategoryGroupsService = new CategoryGroups();

        $import = $schematicCategoryGroupsService->import($groupDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidCategoryGroups()
    {
        return [
            'emptyArray' => [
                'CategoryGroups' => [],
                'expectedResult' => [],
            ],
            'single group' => [
                'CategoryGroups' => [
                    'group1' => $this->getMockCategoryGroup(1),
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'hasUrls' => null,
                        'template' => null,
                        'maxLevels' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                                'nestedUrlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
            'multiple groups' => [
                'CategoryGroups' => [
                    'group1' => $this->getMockCategoryGroup(1),
                    'group2' => $this->getMockCategoryGroup(2),
                ],
                'expectedResult' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'hasUrls' => null,
                        'template' => null,
                        'maxLevels' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                                'nestedUrlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                    'groupHandle2' => [
                        'name' => 'groupName2',
                        'hasUrls' => null,
                        'template' => null,
                        'maxLevels' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                                'nestedUrlFormat' => null,
                            ],
                        ],
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
    public function provideValidCategoryGroupDefinitions()
    {
        return [
            'emptyArray' => [
                'groupDefinitions' => [],
            ],
            'single group' => [
                'groupDefinitions' => [
                    'groupHandle1' => [
                        'name' => 'groupName1',
                        'hasUrls' => false,
                        'template' => '',
                        'maxLevels' => 3,
                        'locales' => [
                            'en' => [
                                'urlFormat' => '',
                                'nestedUrlFormat' => '',
                            ],
                        ],
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
     * @return Mock|CategoryGroupModel
     */
    private function getMockCategoryGroup($groupId)
    {
        $mockCategoryGroup = $this->getMockBuilder(CategoryGroupModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockCategoryGroup->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $groupId],
                ['fieldLayoutId', $groupId],
                ['handle', 'groupHandle'.$groupId],
                ['name', 'groupName'.$groupId],
            ]);

        $mockCategoryGroup->expects($this->any())
            ->method('getLocales')
            ->willReturn([$this->getMockCategoryGroupLocale()]);

        $mockCategoryGroup->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockCategoryGroup;
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
     * @return Mock|CategoriesService
     */
    private function setMockCategoriesService()
    {
        $mockCategoriesService = $this->getMockBuilder(CategoriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllGroups', 'saveGroup', 'deleteGroupById'])
            ->getMock();

        $mockCategoriesService->expects($this->any())
            ->method('getAllGroups')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'categories', $mockCategoriesService);

        return $mockCategoriesService;
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
     * @return Mock|CategoryGroupLocaleModel
     */
    private function getMockCategoryGroupLocale()
    {
        $mockCategoryGroupLocale = $this->getMockBuilder(CategoryGroupLocaleModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockCategoryGroupLocale->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['locale', 'en'],
            ]);

        return $mockCategoryGroupLocale;
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
