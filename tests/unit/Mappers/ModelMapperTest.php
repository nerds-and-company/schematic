<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Class ModelMapperTest.
 *
 * @TODO: Isolate from category groups
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ModelMapperTest extends Unit
{
    /**
     * @var CategoryGroups
     */
    private $mapper;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        Craft::$app->sites->expects($this->any())
                  ->method('getSiteByHandle')
                  ->willReturn($this->getMockSite());

        $this->mapper = new ModelMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideValidCategoryGroups
     *
     * @param CategoryGroupModel[] $groups
     * @param array                $expectedResult
     */
    public function testSuccessfulExport(array $groups, array $expectedResult = [])
    {
        $actualResult = $this->mapper->export($groups);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testSuccessfulImport(array $groupDefinitions, array $existingGroups, int $saveCount)
    {
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->mapper->import($groupDefinitions, $existingGroups);
    }

    /**
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWithForceOption(array $groupDefinitions, array $existingGroups, int $saveCount, int $deleteCount)
    {
        Schematic::$force = true;
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->mapper->import($groupDefinitions, $existingGroups);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidCategoryGroups()
    {
        $mockCategoryGroup1 = $this->getMockCategoryGroup(1);
        $mockCategoryGroup2 = $this->getMockCategoryGroup(2);

        return [
            'emptyArray' => [
                'categoryGroups' => [],
                'expectedResult' => [],
            ],
            'single group' => [
                'categoryGroups' => [
                    'group1' => $mockCategoryGroup1,
                ],
                'expectedResult' => [
                    'groupHandle1' => $this->getMockCategoryGroupDefinition($mockCategoryGroup1),
                ],
            ],
            'multiple groups' => [
                'categoryGroups' => [
                    'group1' => $mockCategoryGroup1,
                    'group2' => $mockCategoryGroup2,
                ],
                'expectedResult' => [
                    'groupHandle1' => $this->getMockCategoryGroupDefinition($mockCategoryGroup1),
                    'groupHandle2' => $this->getMockCategoryGroupDefinition($mockCategoryGroup2),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidCategoryGroupDefinitions()
    {
        $mockCategoryGroup1 = $this->getMockCategoryGroup(1);
        $mockCategoryGroup2 = $this->getMockCategoryGroup(2);

        return [
            'emptyArray' => [
                'groupDefinitions' => [],
                'existingGroups' => [
                    $mockCategoryGroup1,
                ],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single new group' => [
                'groupDefinitions' => [
                    'groupHandle1' => $this->getMockCategoryGroupDefinition($mockCategoryGroup1),
                    'groupHandle2' => $this->getMockCategoryGroupDefinition($mockCategoryGroup2),
                ],
                'existingGroups' => [
                    $mockCategoryGroup1,
                ],
                'saveCount' => 1,
                'deleteCount' => 0,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * Expect a number of group saves.
     *
     * @param int $saveCount
     */
    private function expectSaves(int $saveCount)
    {
        Craft::$app->categories
                   ->expects($this->exactly($saveCount))
                   ->method('saveGroup')
                   ->willReturn(true);
    }

    /**
     * Expect a number of group deletes.
     *
     * @param int $deleteCount
     */
    private function expectDeletes(int $deleteCount)
    {
        Craft::$app->categories
                    ->expects($this->exactly($deleteCount))
                    ->method('deleteGroupById');
    }
}
