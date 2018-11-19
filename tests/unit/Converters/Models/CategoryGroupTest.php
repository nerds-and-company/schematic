<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\elements\Category;
use craft\models\CategoryGroup as CategoryGroupModel;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\FieldLayout;
use craft\models\Site;
use Codeception\Test\Unit;

/**
 * Class CategoryGroupTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroupTest extends Unit
{
    /**
     * @var CategoryGroup
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
        $this->converter = new CategoryGroup();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideCategoryGroups
     *
     * @param CategoryGroupModel $group
     * @param array              $definition
     */
    public function testGetRecordDefinition(CategoryGroupModel $group, array $definition)
    {
        $result = $this->converter->getRecordDefinition($group);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideCategoryGroups
     *
     * @param CategoryGroupModel $group
     * @param array              $definition
     * @param Site|null          $site
     */
    public function testSetRecordAttributes(CategoryGroupModel $group, array $definition, $site)
    {
        $newGroup = $this->getMockBuilder(CategoryGroupModel::class)
                         ->setMethods(['setSiteSettings'])
                         ->getMock();

        $newGroup->expects($this->exactly(1))
                 ->method('setSiteSettings');

        Craft::$app->sites->expects($this->any())
                           ->method('getSiteByHandle')
                           ->willReturn($site);

        $this->converter->setRecordAttributes($newGroup, $definition, []);

        $this->assertSame($group->name, $newGroup->name);
        $this->assertSame($group->handle, $newGroup->handle);
    }

    /**
     * @dataProvider provideCategoryGroups
     *
     * @param CategoryGroupModel $group
     * @param array              $definition
     */
    public function testSaveRecord(CategoryGroupModel $group, array $definition)
    {
        Craft::$app->categories->expects($this->exactly(1))
                               ->method('saveGroup')
                               ->with($group)
                               ->willReturn(true);

        $result = $this->converter->saveRecord($group, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideCategoryGroups
     *
     * @param CategoryGroupModel $group
     */
    public function testDeleteRecord(CategoryGroupModel $group)
    {
        Craft::$app->categories->expects($this->exactly(1))
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
    public function provideCategoryGroups()
    {
        $mockCategoryGroup = $this->getMockCategoryGroup(1);

        return [
            'category group with site' => [
                'group' => $mockCategoryGroup,
                'definition' => $this->getMockCategoryGroupDefinition($mockCategoryGroup),
                'site' => $this->getMockSite(),
            ],
            'category group without site' => [
                'group' => $mockCategoryGroup,
                'definition' => $this->getMockCategoryGroupDefinition($mockCategoryGroup),
                'site' => null,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param CategoryGroupModel $mockCategoryGroup
     *
     * @return array
     */
    private function getMockCategoryGroupDefinition(CategoryGroupModel $mockCategoryGroup)
    {
        return [
            'class' => get_class($mockCategoryGroup),
            'attributes' => [
                'name' => $mockCategoryGroup->name,
                'handle' => $mockCategoryGroup->handle,
                'maxLevels' => 3,
            ],
            'fieldLayout' => [
                'type' => Category::class,
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
     * @param int $groupId
     *
     * @return Mock|CategoryGroupModel
     */
    private function getMockCategoryGroup(int $groupId)
    {
        $mockGroup = $this->getMockBuilder(CategoryGroupModel::class)
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
        $mockFieldLayout->type = Category::class;

        $mockGroup->expects($this->any())
                  ->method('getFieldLayout')
                  ->willReturn($mockFieldLayout);

        $mockSiteSettings = $this->getMockSiteSettings();

        $mockGroup->expects($this->any())
                  ->method('getSiteSettings')
                  ->willReturn([$mockSiteSettings]);

        return $mockGroup;
    }

    /**
     * Get mock siteSettings.
     *
     * @param string $class
     *
     * @return Mock|CategoryGroup_SiteSettings
     */
    private function getMockSiteSettings()
    {
        $mockSiteSettings = $this->getMockBuilder(CategoryGroup_SiteSettings::class)
                                 ->setMethods(['getSite'])
                                 ->getMock();

        $mockSiteSettings->expects($this->any())
          ->method('getSite')
          ->willReturn($this->getMockSite());

        return $mockSiteSettings;
    }

    /**
     * Get a mock site.
     *
     * @return Mock|Site
     */
    private function getMockSite()
    {
        return $this->getMockBuilder(Site::class)->getMock();
    }
}
