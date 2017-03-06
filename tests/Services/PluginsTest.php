<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\Exception;
use Craft\PluginsService;
use Craft\UpdatesService;
use Craft\BasePlugin;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class PluginsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\Plugins
 * @covers ::__construct
 * @covers ::<!public>
 */
class PluginsTest extends BaseTest
{
    /**
     * @var Plugins
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
        $this->setComponent(Craft::app(), 'plugins', $mockPluginsService);
        $mockUpdatesService = $this->getMockUpdatesService();
        $this->setComponent(Craft::app(), 'updates', $mockUpdatesService);
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
     * @return UpdatesService|Mock
     */
    public function getMockUpdatesService()
    {
        $mock = $this->getMockBuilder(UpdatesService::class)->getMock();
        $mock->expects($this->any())->method('updateDatabase')->willReturn(array('success' => true));

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

        $this->mockMultipleServices(false);

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
        $this->mockMultipleServices();

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
            ->willReturn((object) ['attributes' => $data[$this->pluginHandle]['settings']]);

        $mockPluginsService = $this->getMockPluginsService();
        $mockPluginsService->expects($this->any())
            ->method('getPlugins')
            ->willReturn([$this->pluginHandle => $mockBasePlugin]);

        $this->setComponent(Craft::app(), 'plugins', $mockPluginsService);

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
        return [
            $this->pluginHandle => [
                'isInstalled' => true,
                'isEnabled' => true,
                'settings' => [
                    'pluginName' => 'Menu',
                    'canDoActions' => '',
                    'quietErrors' => '',
                ],
            ],
        ];
    }
}
