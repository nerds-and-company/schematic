<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_MockObject_Matcher_Invocation as Invocation;

/**
 * Class Schematic_UsersServiceTest.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\SchematicService
 * @covers ::<!public>
 */
class Schematic_SchematicServiceTest extends BaseTest
{
    /**
     * @var SchematicService
     */
    private $schematicService;


    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicService = new SchematicService();
        $this->mockServices();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../SchematicPlugin.php';
        require_once __DIR__.'/../models/Schematic_DataModel.php';
        require_once __DIR__.'/../models/Schematic_ResultModel.php';
        require_once __DIR__.'/../services/Schematic_AbstractService.php';
        require_once __DIR__.'/../services/Schematic_AssetsService.php';
        require_once __DIR__.'/../services/Schematic_FieldsService.php';
        require_once __DIR__.'/../services/Schematic_GlobalsService.php';
        require_once __DIR__.'/../services/Schematic_PluginsService.php';
        require_once __DIR__.'/../services/Schematic_SectionsService.php';
        require_once __DIR__.'/../services/Schematic_UserGroupsService.php';
        require_once __DIR__.'/../services/Schematic_UsersService.php';
        require_once __DIR__.'/../services/SchematicService.php';
    }

    /**
     * @return string
     */
    private function getYamlTestFile()
    {
        return __DIR__ . '/data/test_schema.yml';
    }

    /**
     * @param string $handle
     * @param Mock $mock
     */
    private function setCraftComponent($handle, Mock $mock)
    {
        $this->setComponent(craft(), $handle, $mock);
    }

    /**
     * @return Mock|\Craft\FieldsService
     */
    public function getMockFieldsService()
    {
        $mock = $this->getMockBuilder('Craft\FieldsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllGroups')->willReturn(array());

        return $mock;
    }

    /**
     * @param string $class
     * @param string $method
     * @param Invocation $invocation
     * @param mixed $returnValue
     * @return Mock
     */
    public function getDynamicallyMockedService($class, $method, Invocation $invocation, $returnValue) {
        $mock = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();

        $mock->expects($invocation)->method($method)->willReturn($returnValue);

        return $mock;
    }

    /**
     * @return Mock|\Craft\GlobalsService
     */
    public function getMockGlobalsService()
    {
        $mock = $this->getMockBuilder('Craft\GlobalsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllSets')->willReturn(array());

        return $mock;
    }

    /**
     * @return Mock|\Craft\SectionsService
     */
    public function getMockSectionsService()
    {
        $mock = $this->getMockBuilder('Craft\SectionsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))->method('getAllSections')->willReturn(array());

        return $mock;
    }

    /**
     * @return Mock|\Craft\Schematic_ResultModel
     */
    public function getMockResultModel()
    {
        $mock = $this->getMockBuilder('Craft\Schematic_ResultModel')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Creates mock for service
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
     * @return Schematic_AbstractService|Mock
     */
    public function getMockAbstractService()
    {
        $mock = $this->getMockBuilder('Craft\Schematic_AbstractService')->getMock();

        $mock->expects($this->any())->method('import')->willReturn($this->getMockResultModel());
        $mock->expects($this->any())->method('export')->willReturn($this->getMockResultModel());

        return $mock;
    }

    /**
     * @return PluginsService|Mock
     */
    public function getMockPluginsService()
    {
        $mock = $this->getMockBuilder('Craft\PluginsService')
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
     * Mock all required services
     */
    private function mockServices()
    {
        $this->createMockService('Craft\Schematic_AssetsService', 'schematic_assets');
        $this->createMockService('Craft\Schematic_FieldsService', 'schematic_fields');
        $this->createMockService('Craft\Schematic_GlobalsService', 'schematic_globals');
        $this->createMockService('Craft\Schematic_PluginsService', 'schematic_plugins');
        $this->createMockService('Craft\Schematic_SectionsService', 'schematic_sections');
        $this->createMockService('Craft\Schematic_UserGroupsService', 'schematic_userGroups');
        $this->createMockService('Craft\Schematic_UsersService', 'schematic_users');

        $mockPluginsService = $this->getMockPluginsService();
        $this->setCraftComponent('plugins', $mockPluginsService);
    }

    /**
     * Test import from Yaml
     * @covers ::importFromYaml
     */
    public function testImportFromYamlWithForce()
    {
       $results = $this->schematicService->importFromYaml($this->getYamlTestFile(), true);
       $this->assertFalse($results->hasErrors());
    }

    /**
     * @param $service
     * @return Mock
     */
    private function getMockAllGroupsMethodService($service)
    {
        return $this->getDynamicallyMockedService($service, 'getAllGroups', $this->exactly(1), array());
    }

    /**
     * Prep export services
     */
    private function prepExportMockServices()
    {
        $mockPluginsService = $this->getMockPluginsService();
        $this->setCraftComponent('plugins', $mockPluginsService);

        $mockFieldsService = $this->getMockAllGroupsMethodService('Craft\FieldsService');
        $this->setCraftComponent('fields', $mockFieldsService);

        $mockSectionsService = $this->getMockSectionsService();
        $this->setCraftComponent('sections', $mockSectionsService);

        $mockGlobalsService = $this->getMockGlobalsService();
        $this->setCraftComponent('globals', $mockGlobalsService);

        $mockUserGroupsService = $this->getMockAllGroupsMethodService('Craft\UserGroupsService');
        $this->setCraftComponent('userGroups', $mockUserGroupsService);
    }

    /**
     * Test export to yml
     * @covers ::exportToYaml
     */
    public function testExportFromYaml()
    {
        $this->prepExportMockServices();

        $results = $this->schematicService->exportToYaml($this->getYamlTestFile());
        $this->assertFalse($results->hasErrors());
    }

    /**
     * Test export to yml with error writing to file
     * @covers ::exportToYaml
     */
    public function testExportFromYamlWithFileError()
    {
        $this->prepExportMockServices();

        $results = $this->schematicService->exportToYaml('non-existing-folder/not-a-file', false);
        $this->assertTrue($results->hasErrors());
    }
}
