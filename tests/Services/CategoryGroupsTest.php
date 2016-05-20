<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\CategoryGroupLocaleModel;
use Craft\CategoryGroupModel;
use Craft\Craft;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class CategoryGroupsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2016, Nerds & Company
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
     * @param string[] $groupPermissions
     * @param array $expectedResult
     */
    public function testSuccessfulExport(array $groups, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicCategoryGroupsService = new CategoryGroups();

        $actualResult = $schematicCategoryGroupsService->export($groups);

        $this->assertSame($expectedResult, $actualResult);
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
                          'fields' => []
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
                          'fields' => []
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
                          'fields' => []
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
                ['handle', 'groupHandle' . $groupId],
                ['name', 'groupName' . $groupId],
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

        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFields);

        return $mockSchematicFields;
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
}
