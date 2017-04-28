<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\CategoriesService;
use Craft\Craft;
use Craft\FieldsService;
use Craft\GlobalsService;
use Craft\SectionsService;
use Craft\TagsService;
use Craft\UserGroupsService;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use stdClass;

/**
 * Class SourcesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\Sources
 * @covers ::<!public>
 */
class SourcesTest extends BaseTest
{
    /**
     * Set up mock services
     */
    protected function setUp()
    {
        $this->setMockSectionsService();
        $this->setMockCategoriesService();
        $this->setMockUserGroupsService();
        $this->setMockSchematicAssetSources();
        $this->setMockTagsService();
        $this->setMockFieldsService();
        $this->setMockGlobalsService();
    }

    /**
     * @covers ::getMappedSources
     * @covers ::getSource
     * @dataProvider provideValidSources
     *
     * @param array  $idSources
     * @param array  $handleSources
     * @param string $fieldType
     */
    public function testGetMappedSourcesFromIdToHandle(array $idSources, array $handleSources, $fieldType = false)
    {
        $schematicSourcesService = new Sources();

        $result = $schematicSourcesService->getMappedSources($fieldType, $idSources, 'id', 'handle');
        $this->assertSame($handleSources, $result);
    }

    /**
     * @covers ::getMappedSources
     * @covers ::getSource
     * @dataProvider provideValidSources
     *
     * @param array  $idSources
     * @param array  $handleSources
     * @param string $fieldType
     */
    public function testGetMappedSourcesFromHandleToId(array $idSources, array $handleSources, $fieldType = false)
    {
        $schematicSourcesService = new Sources();

        $result = $schematicSourcesService->getMappedSources($fieldType, $handleSources, 'handle', 'id');
        $this->assertSame($idSources, $result);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidSources()
    {
        return [
            'emptySources' => [
                'sources' => [],
                'expectedResult' => [],
            ],
            'allSources' => [
                'idSources' => ['*'],
                'handleSources' => ['*'],
            ],
            'sectionSources' => [
                'idSources' => [
                    'singles',
                    'section:1',
                    'section:3',
                ],
                'handleSources' => [
                    'singles',
                    'section:sectionHandle-1',
                    'section:sectionHandle-3',
                ],
            ],
            'userSources' => [
                'idSources' => [
                    'group:1',
                    'group:3',
                ],
                'handleSources' => [
                    'group:userGroupHandle-1',
                    'group:userGroupHandle-3',
                ],
                'fieldType' => 'Users',
            ],
            'categoryGroupSources' => [
                'idSources' => [
                    'group:1',
                    'group:3',
                ],
                'handleSources' => [
                    'group:categoryGroupHandle-1',
                    'group:categoryGroupHandle-3',
                ],
            ],
            'categoryGroupPermissions' => [
                'idSources' => [
                    'editCategories:1',
                    'editCategories:2',
                ],
                'handleSources' => [
                    'editCategories:categoryGroupHandle-1',
                    'editCategories:categoryGroupHandle-2',
                ],
            ],
            'folderSources' => [
                'idSources' => [
                    'folder:1',
                    'folder:2',
                ],
                'handleSources' => [
                    'folder:assetSourceHandle-1',
                    'folder:assetSourceHandle-2',
                ],
            ],
            'folderPermissions' => [
                'idSources' => [
                    'createSubfoldersInAssetSource:1',
                    'removeFromAssetSource:2',
                    'uploadToAssetSource:1',
                    'viewAssetSource:2',
                ],
                'handleSources' => [
                    'createSubfoldersInAssetSource:assetSourceHandle-1',
                    'removeFromAssetSource:assetSourceHandle-2',
                    'uploadToAssetSource:assetSourceHandle-1',
                    'viewAssetSource:assetSourceHandle-2',
                ],
            ],
            'tagGroupSources' => [
                'idSources' => [
                    'taggroup:1',
                    'taggroup:2',
                ],
                'handleSources' => [
                    'taggroup:tagGroupHandle-1',
                    'taggroup:tagGroupHandle-2',
                ],
            ],
            'fieldSources' => [
                'idSources' => [
                    'field:1',
                    'field:2',
                ],
                'handleSources' => [
                    'field:fieldHandle-1',
                    'field:fieldHandle-2',
                ],
            ],
            'globalSetPermissions' => [
                'idSources' => [
                    'editGlobalSet:1',
                    'editGlobalSet:2',
                ],
                'handleSources' => [
                    'editGlobalSet:globalSetHandle-1',
                    'editGlobalSet:globalSetHandle-2',
                ],
            ],
            'localePermissions' => [
                'idSources' => [
                    'editLocale:en',
                    'editLocale:nl',
                ],
                'handleSources' => [
                    'editLocale:en',
                    'editLocale:nl',
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * Set the mock sections service with getSectionById and getSectionByHandle
     */
    private function setMockSectionsService()
    {
        $mock = $this->getMockBuilder(SectionsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getSectionById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'sectionHandle-'.$id);
        });

        $mock->expects($this->any())->method('getSectionByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('sections', $mock);
    }

    /**
     * Set the mock categories service with getGroupById and getGroupByHandle
     */
    private function setMockCategoriesService()
    {
        $mock = $this->getMockBuilder(CategoriesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getGroupById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'categoryGroupHandle-'.$id);
        });

        $mock->expects($this->any())->method('getGroupByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('categories', $mock);
    }

    /**
     * Set the mock usergroups service with getGroupById and getGroupByHandle
     */
    private function setMockUserGroupsService()
    {
        $mock = $this->getMockBuilder(UserGroupsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getGroupById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'userGroupHandle-'.$id);
        });

        $mock->expects($this->any())->method('getGroupByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('userGroups', $mock);
    }

    /**
     * Set the mock schematic_assetsources with getGroupById and getGroupByHandle
     */
    private function setMockSchematicAssetSources()
    {
        $mock = $this->getMockBuilder(AssetSources::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getSourceById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'assetSourceHandle-'.$id);
        });

        $mock->expects($this->any())->method('getSourceByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('schematic_assetSources', $mock);
    }

    /**
     * Set mock tags service
     */
    private function setMockTagsService()
    {
        $mock = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getTagGroupById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'tagGroupHandle-'.$id);
        });

        $mock->expects($this->any())->method('getTagGroupByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('tags', $mock);
    }

    /**
     * Set mock fields service
     */
    private function setMockFieldsService()
    {
        $mock = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getFieldById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'fieldHandle-'.$id);
        });

        $mock->expects($this->any())->method('getFieldByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('fields', $mock);
    }

    /**
     * Set mock fields service
     */
    private function setMockGlobalsService()
    {
        $mock = $this->getMockBuilder(GlobalsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('getSetById')->willReturnCallback(function ($id) {
            return $this->getMockObject($id, 'globalSetHandle-'.$id);
        });

        $mock->expects($this->any())->method('getSetByHandle')->willReturnCallback(function ($handle) {
            $id = explode('-', $handle)[1];
            return $this->getMockObject($id, $handle);
        });

        Craft::app()->setComponent('globals', $mock);
    }

    /**
     * get a mock object with id and handle
     *
     * @param  int $id
     * @param  string $handle
     * @return stdClass
     */
    private function getMockObject($id, $handle)
    {
        $mockObject = new stdClass();
        $mockObject->id = $id;
        $mockObject->handle = $handle;

        return $mockObject;
    }
}
