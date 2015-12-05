<?php

namespace NerdsAndCompany\SchematicTests\Services;

use Craft\BaseTest;
use Craft\Exception;
use Craft\PluginsService;
use Craft\MigrationsService;
use Craft\UpdatesService;
use Craft\BasePlugin;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Services\Plugins;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_PluginsServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass Craft\Schematic_PluginsService
 * @covers ::__construct
 * @covers ::<!public>
 */
class PluginsTest extends BaseTest
{
    /**
     * @var Schematic_PluginsService
     */
    private $schematicPluginsService;

    /**
     * @var string
     */
    private $pluginHandle;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicPluginsService = new Plugins();
    }

    /**
     * Prevent code duplication by mocking multiple services.
     *
     * @param bool $returnPlugin
     * @param bool $installPluginResponse
     */
    public function mockMultipleServices(
        $returnPlugin = true,
        $installPluginResponse = true
    ) {
        $mockPluginsService = $this->getMockPluginsService($returnPlugin, $installPluginResponse);
        $this->setComponent(craft(), 'plugins', $mockPluginsService);
        $mockMigrationsService = $this->getMockMigrationsService();
        $this->setComponent(craft(), 'migrations', $mockMigrationsService);
        $mockUpdatesService = $this->getMockUpdatesService();
        $this->setComponent(craft(), 'updates', $mockUpdatesService);
    }

    /**
     * @param bool $returnPlugin
     * @param bool $installPluginResponse
     * @param bool $enablePluginResponse
     * @param bool $disablePluginResponse
     * @param bool $uninstallPluginResponse
     *
     * @return PluginsService|Mock
     */
    public function getMockPluginsService(
        $returnPlugin = true,
        $installPluginResponse = true,
        $enablePluginResponse = true,
        $disablePluginResponse = true,
        $uninstallPluginResponse = true
    ) {
        $mock = $this->getMockBuilder(PluginsService::class)->getMock();

        $mock->expects($this->any())->method('getPlugin')->willReturn(($returnPlugin) ? $this->getMockBasePlugin() : null);

        if ($installPluginResponse) {
            $mock->expects($this->any())->method('installPlugin')->willReturn($installPluginResponse);
        } else {
            $mock->expects($this->any())->method('installPlugin')->willThrowException(new Exception());
        }

        $mock->expects($this->any())->method('enablePlugin')->willReturn($enablePluginResponse);
        $mock->expects($this->any())->method('disablePlugin')->willReturn($disablePluginResponse);
        $mock->expects($this->any())->method('uninstallPlugin')->willReturn($uninstallPluginResponse);

        return $mock;
    }

    /**
     * @return MigrationsService|Mock
     */
    public function getMockMigrationsService()
    {
        $mock = $this->getMockBuilder(MigrationsService::class)->getMock();
        $mock->expects($this->any())->method('runToTop')->willReturn(true);

        return $mock;
    }

    /**
     * @return UpdatesService|Mock
     */
    public function getMockUpdatesService()
    {
        $mock = $this->getMockBuilder(UpdatesService::class)->getMock();
        $mock->expects($this->any())->method('setNewPluginInfo')->willReturn(true);

        return $mock;
    }

    /**
     * @return Mock|BasePlugin
     */
    public function getMockBasePlugin()
    {
        $mock = $this->getMockBuilder(BasePlugin::class)->getMock();

        $this->pluginHandle = get_class($mock);

        return $mock;
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithInstalledPlugins()
    {
        $data = $this->getPluginsData();

        $this->mockMultipleServices();

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithInstalledDisabledPlugins()
    {
        $this->getMockBasePlugin();

        $data = $this->getPluginsData();
        $data[$this->pluginHandle]['isEnabled'] = false;

        $this->mockMultipleServices();

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithMissingPlugin()
    {
        $data = $this->getPluginsData();

        $mockPluginsService = $this->getMockPluginsService(false);
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithInstallException()
    {
        $data = $this->getPluginsData();

        $this->mockMultipleServices(true, false);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithNotInstalledPlugin()
    {
        $mockPluginsService = $this->getMockPluginsService();
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $this->getMockBasePlugin();

        $data = $this->getPluginsData();
        $data[$this->pluginHandle]['isInstalled'] = false;

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test export functionality.
     *
     * @covers ::export
     */
    public function testExport()
    {
        $mockBasePlugin = $this->getMockBasePlugin();
        $mockBasePlugin->isInstalled = true;
        $mockBasePlugin->isEnabled = true;

        $data = $this->getPluginsData();

        $mockBasePlugin
            ->expects($this->any())
            ->method('getSettings')
            ->willReturn((Object) array('attributes' => $data[$this->pluginHandle]['settings']));

        $mockPluginsService = $this->getMockPluginsService();
        $mockPluginsService->expects($this->any())
            ->method('getPlugins')
            ->willReturn(array($this->pluginHandle => $mockBasePlugin));

        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $export = $this->schematicPluginsService->export();
        $this->assertEquals($data, $export);
    }

    /**
     * Returns plugins data.
     *
     * @return array
     */
    public function getPluginsData()
    {
        return array(
            $this->pluginHandle => array(
                'isInstalled'       => true,
                'isEnabled'         => true,
                'settings'          => array(
                    'pluginName'    => 'Menu',
                    'canDoActions'  => '',
                    'quietErrors'   => '',
                ),
            ),
        );
    }
}
