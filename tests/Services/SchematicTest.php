<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\AssetSourcesService;
use Craft\AssetTransformsService;
use Craft\CategoriesService;
use Craft\Craft;
use Craft\FieldsService;
use Craft\GlobalsService;
use Craft\IOHelper;
use Craft\PluginsService;
use Craft\SectionsService;
use Craft\TagsService;
use Craft\UserGroupsService;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Models\Data;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_MockObject_Matcher_Invocation as Invocation;

/**
 * Class Schematic_UsersServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\Schematic
 * @covers ::<!public>
 */
class SchematicTest extends BaseTest
{
    /**
     * @var Schematic
     */
    private $schematicService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicService = new Schematic();
        $this->mockServices();
    }

    /**
     * @return string
     */
    private function getYamlTestFile()
    {
        return __DIR__.'/../data/test_schema.yml';
    }

    /**
     * @return string
     */
    private function getYamlExportFile()
    {
        return __DIR__.'/../data/test_schema_export.yml';
    }

    /**
     * @param string $handle
     * @param Mock   $mock
     */
    private function setCraftComponent($handle, Mock $mock)
    {
        $this->setComponent(Craft::app(), $handle, $mock);
    }

    /**
     * @return Mock|FieldsService
     */
    public function getMockFieldsService()
    {
        $mock = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllGroups')->willReturn([]);

        return $mock;
    }

    /**
     * @param string     $class
     * @param string     $method
     * @param Invocation $invocation
     * @param mixed      $returnValue
     *
     * @return Mock
     */
    public function getDynamicallyMockedService($class, $method, Invocation $invocation, $returnValue)
    {
        $mock = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();

        $mock->expects($invocation)->method($method)->willReturn($returnValue);

        return $mock;
    }

    /**
     * @return Mock|GlobalsService
     */
    public function getMockGlobalsService()
    {
        $mock = $this->getMockBuilder(GlobalsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllSets')->willReturn([]);

        return $mock;
    }

    /**
     * @return Mock|SectionsService
     */
    public function getMockSectionsService()
    {
        $mock = $this->getMockBuilder(SectionsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllSections')->willReturn([]);

        return $mock;
    }

    /**
     * @return Mock|Result
     */
    public function getMockResultModel()
    {
        $mock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Creates mock for service.
     *
     * @param string $service
     * @param string $handle
     */
    private function createMockService($service, $handle)
    {
        $mock = $this->getMockBuilder($service)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('import')->willReturn($this->getMockResultModel());
        $mock->expects($this->any())->method('export')->willReturn($this->getMockResultModel());

        $this->setCraftComponent($handle, $mock);
    }

    /**
     * @return Base|Mock
     */
    public function getMockAbstractService()
    {
        $mock = $this->getMockBuilder(Base::class)->getMock();

        $mock->expects($this->any())->method('import')->willReturn($this->getMockResultModel());
        $mock->expects($this->any())->method('export')->willReturn($this->getMockResultModel());

        return $mock;
    }

    /**
     * @return PluginsService|Mock
     */
    public function getMockPluginsService()
    {
        $mock = $this->getMockBuilder(PluginsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())->method('call')->willReturn([
            'TestPlugin' => [
                'test_service' => $this->getMockAbstractService(),
            ],
        ]);

        return $mock;
    }

    /**
     * @return Mock|AssetSourcesService
     */
    public function getMockAssetSourcesService()
    {
        $mock = $this->getMockBuilder(AssetSourcesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllSources')->willReturn([]);

        return $mock;
    }

    /**
     * @return Mock|AssetTransformsService
     */
    public function getMockAssetTransformsService()
    {
        $mock = $this->getMockBuilder(AssetTransformsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllTransforms')->willReturn([]);

        return $mock;
    }

    /**
     * @return Mock|CategoriesService
     */
    public function getMockCategoriesService()
    {
        $mock = $this->getMockBuilder(CategoriesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllGroups')->willReturn([]);

        return $mock;
    }

    /**
     * @return Mock|TagsService
     */
    public function getMockTagsService()
    {
        $mock = $this->getMockBuilder(TagsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllTagGroups')->willReturn([]);

        return $mock;
    }

    /**
     * Mock all required services.
     */
    private function mockServices()
    {
        $this->createMockService(Locales::class, 'schematic_locales');
        $this->createMockService(AssetSources::class, 'schematic_assetSources');
        $this->createMockService(AssetTransforms::class, 'schematic_assetTransforms');
        $this->createMockService(Fields::class, 'schematic_fields');
        $this->createMockService(GlobalSets::class, 'schematic_globalSets');
        $this->createMockService(Plugins::class, 'schematic_plugins');
        $this->createMockService(Sections::class, 'schematic_sections');
        $this->createMockService(UserGroups::class, 'schematic_userGroups');
        $this->createMockService(Users::class, 'schematic_users');
        $this->createMockService(CategoryGroups::class, 'schematic_categoryGroups');
        $this->createMockService(TagGroups::class, 'schematic_tagGroups');
        $this->createMockService(ElementIndexSettings::class, 'schematic_elementIndexSettings');

        $mockPluginsService = $this->getMockPluginsService();
        $this->setCraftComponent('plugins', $mockPluginsService);
    }

    /**
     * Test import from Yaml.
     *
     * @covers ::importFromYaml
     */
    public function testImportFromYamlWithForce()
    {
        $results = $this->schematicService->importFromYaml($this->getYamlTestFile(), null, true);
        $this->assertFalse($results->hasErrors());
    }

    /**
     * Test import from yml excluding data types.
     *
     * @covers ::exportToYaml
     */
    public function testImportFromYamlExcludingDataTypes()
    {
        //We do not want to import these DataTypes, so these import functions should not be called.
        Craft::app()->schematic_users->expects($this->exactly(0))->method('import');
        Craft::app()->schematic_plugins->expects($this->exactly(0))->method('import');

        //We do want to import these DataTypes, so these import functions should be called.
        Craft::app()->schematic_userGroups->expects($this->exactly(1))->method('import');
        Craft::app()->schematic_assetSources->expects($this->exactly(1))->method('import');

        $results = $this->schematicService->importFromYaml($this->getYamlTestFile(), null, false, ['assetSources', 'userGroups']);
        $this->assertFalse($results->hasErrors());
    }

    /**
     * @param $service
     *
     * @return Mock
     */
    private function getMockAllGroupsMethodService($service)
    {
        return $this->getDynamicallyMockedService($service, 'getAllGroups', $this->exactly(1), []);
    }

    /**
     * Prep export services.
     */
    private function prepExportMockServices()
    {
        $mockPluginsService = $this->getMockPluginsService();
        $this->setCraftComponent('plugins', $mockPluginsService);

        $mockFieldsService = $this->getMockAllGroupsMethodService(FieldsService::class);
        $this->setCraftComponent('fields', $mockFieldsService);

        $mockSectionsService = $this->getMockSectionsService();
        $this->setCraftComponent('sections', $mockSectionsService);

        $mockGlobalsService = $this->getMockGlobalsService();
        $this->setCraftComponent('globals', $mockGlobalsService);

        $mockUserGroupsService = $this->getMockAllGroupsMethodService(UserGroupsService::class);
        $this->setCraftComponent('userGroups', $mockUserGroupsService);

        $mockAssetSourcesService = $this->getMockAssetSourcesService();
        $this->setCraftComponent('assetSources', $mockAssetSourcesService);

        $mockAssetTransformsService = $this->getMockAssetTransformsService();
        $this->setCraftComponent('assetTransforms', $mockAssetTransformsService);

        $mockCategoriesService = $this->getMockCategoriesService();
        $this->setCraftComponent('categories', $mockCategoriesService);

        $mockTagsService = $this->getMockTagsService();
        $this->setCraftComponent('tags', $mockTagsService);
    }

    /**
     * Test export to yml.
     *
     * @covers ::exportToYaml
     */
    public function testExportFromYaml()
    {
        $this->prepExportMockServices();

        $results = $this->schematicService->exportToYaml($this->getYamlExportFile());
        $this->assertFalse($results->hasErrors());
    }

    /**
     * Test export to yml excluding data types.
     *
     * @covers ::exportToYaml
     */
    public function testExportToYamlExcludingDataTypes()
    {
        $this->prepExportMockServices();

        $exportableDataTypes = Schematic::getExportableDataTypes();

        $dataTypesToExport = array_diff($exportableDataTypes, ['pluginData']);

        $results = $this->schematicService->exportToYaml($this->getYamlExportFile(), $dataTypesToExport);
        $this->assertFalse($results->hasErrors());

        // Read and process the recently created export YAML file.
        $yaml = IOHelper::getFileContents($this->getYamlExportFile());
        $dataModel = Data::fromYaml($yaml, []);

        // Make sure the excluded data type was not exported.
        $this->assertEmpty($dataModel->pluginData);
    }

    /**
     * Test export to yml with error writing to file.
     *
     * @covers ::exportToYaml
     */
    public function testExportFromYamlWithFileError()
    {
        $this->prepExportMockServices();

        $results = $this->schematicService->exportToYaml('non-existing-folder/not-a-file', 'all', false);
        $this->assertTrue($results->hasErrors());
    }
}
