<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\console\Application;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\FieldLayout;
use craft\models\Site;
use craft\services\Categories;
use craft\services\Fields;
use craft\services\Sites;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Class CategoryGroupsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroupsTest extends Unit
{
    /**
     * @var CategoryGroups
     */
    private $service;

    /**
     * Set the service.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $mockCategoryGroups = $this->getMockBuilder(Categories::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockFields = $this->getMockBuilder(Fields::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockSite = $this->getMockBuilder(Site::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $mockSites = $this->getMockBuilder(Sites::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $mockSites->expects($this->any())
                  ->method('getSiteByHandle')
                  ->willReturn($mockSite);

        $mockApp = $this->getMockBuilder(Application::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $mockApp->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['categories', $mockCategoryGroups],
                ['fields', $mockFields],
                ['sites', $mockSites],
            ]);

        Craft::$app = $mockApp;
        Schematic::$force = false;

        $this->service = new CategoryGroups();
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
        $this->expectList($groups);

        $actualResult = $this->service->export();

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testSuccessfulImport(array $groupDefinitions, array $existingGroups, int $saveCount)
    {
        $this->expectList($existingGroups);
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->service->import($groupDefinitions);
    }

    /**
     * @dataProvider provideValidCategoryGroupDefinitions
     *
     * @param array $groupDefinitions
     */
    public function testImportWithForceOption(array $groupDefinitions, array $existingGroups, int $saveCount, int $deleteCount)
    {
        Schematic::$force = true;
        $this->expectList($existingGroups);
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->service->import($groupDefinitions);
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
                'CategoryGroups' => [],
                'expectedResult' => [],
            ],
            'single group' => [
                'CategoryGroups' => [
                    'group1' => $mockCategoryGroup1,
                ],
                'expectedResult' => [
                    'groupHandle1' => $this->getMockCategoryGroupDefinition($mockCategoryGroup1),
                ],
            ],
            'multiple groups' => [
                'CategoryGroups' => [
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
            'single group' => [
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
     * @param CategoryGroup $$group
     *
     * @return array
     */
    private function getMockCategoryGroupDefinition(CategoryGroup $mockCategoryGroup)
    {
        return [
            'class' => get_class($mockCategoryGroup),
            'attributes' => [
                'name' => $mockCategoryGroup->name,
                'handle' => $mockCategoryGroup->handle,
                'maxLevels' => 3,
            ],
            'fieldLayout' => [
                'fields' => [],
            ],
            'siteSettings' => [
                '' => [
                    'class' => get_class($mockCategoryGroup->getSiteSettings()[0]),
                    'attributes' => [
                        'hasUrls' => null,
                        'uriFormat' => null,
                        'template' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $groupId
     *
     * @return Mock|CategoryGroup
     */
    private function getMockCategoryGroup($groupId)
    {
        $mockGroup = $this->getMockBuilder(CategoryGroup::class)
                                    ->setMethods(['getFieldLayout', 'getSiteSettings'])
                                    ->getMock();
        $mockGroup->setAttributes([
            'id' => $groupId,
            'fieldLayoutId' => $groupId,
            'handle' => 'groupHandle'.$groupId,
            'name' => 'groupName'.$groupId,
            'maxLevels' => 3,
        ]);

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();

        $mockGroup->expects($this->any())
                  ->method('getFieldLayout')
                  ->willReturn($mockFieldLayout);

        $mocksiteSettings = $this->getMockBuilder(CategoryGroup_SiteSettings::class)->setMethods(['getSite'])->getMock();
        $mockSite = $this->getMockBuilder(Site::class)->getMock();

        $mocksiteSettings->expects($this->any())
            ->method('getSite')
            ->willReturn($mockSite);

        $mockGroup->expects($this->any())
                  ->method('getSiteSettings')
                  ->willReturn([$mocksiteSettings]);

        return $mockGroup;
    }

    /**
     * Expect a list of category groups.
     *
     * @param CategoryGroup[] $categoryGroups
     */
    private function expectList(array $categoryGroups)
    {
        Craft::$app->categories
                   ->expects($this->exactly(1))
                   ->method('getAllGroups')
                   ->willReturn($categoryGroups);
    }

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