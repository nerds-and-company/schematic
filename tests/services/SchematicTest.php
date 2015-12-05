<?php

namespace NerdsAndCompany\SchematicTests\Services;

use Craft\BaseTest;
use Craft\FieldsService;
use Craft\GlobalsService;
use Craft\SectionsService;
use Craft\PluginsService;
use Craft\UserGroupsService;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Services;
use NerdsAndCompany\Schematic\Services\Base;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_MockObject_Matcher_Invocation as Invocation;

/**
 * Class Schematic_UsersServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
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
        $this->setComponent(craft(), $handle, $mock);
    }

    /**
     * @return Mock|FieldsService
     */
    public function getMockFieldsService()
    {
        $mock = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllGroups')->willReturn(array());

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

        $mock->expects($this->exactly(1))->method('getAllSets')->willReturn(array());

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

        $mock->expects($this->exactly(1))->method('getAllSections')->willReturn(array());

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

        $mock->expects($this->any())->method('call')->willReturn(array(
            'TestPlugin' => array(
                'test_service' => $this->getMockAbstractService(),
            ),
        ));

        return $mock;
    }

    /**
     * Mock all required services.
     */
    private function mockServices()
    {
        $this->createMockService(Locales::class, 'schematic_locales');
        $this->createMockService(AssetSources::class, 'schematic_assetSources');
        $this->createMockService(Fields::class, 'schematic_fields');
        $this->createMockService(GlobalSets::class, 'schematic_globalSets');
        $this->createMockService(Plugins::class, 'schematic_plugins');
        $this->createMockService(Sections::class, 'schematic_sections');
        $this->createMockService(UserGroups::class, 'schematic_userGroups');
        $this->createMockService(Users::class, 'schematic_users');

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
     * @param $service
     *
     * @return Mock
     */
    private function getMockAllGroupsMethodService($service)
    {
        return $this->getDynamicallyMockedService($service, 'getAllGroups', $this->exactly(1), array());
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
     * Test export to yml with error writing to file.
     *
     * @covers ::exportToYaml
     */
    public function testExportFromYamlWithFileError()
    {
        $this->prepExportMockServices();

        $results = $this->schematicService->exportToYaml('non-existing-folder/not-a-file', false);
        $this->assertTrue($results->hasErrors());
    }
}
