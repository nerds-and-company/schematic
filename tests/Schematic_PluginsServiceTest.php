<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_PluginsServiceTest
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\Schematic_PluginsService
 * @covers ::__construct
 * @covers ::<!public>
 */
class Schematic_PluginsServiceTest extends BaseTest
{
    /**
     * @var Schematic_PluginsService
     */
    private $schematicPluginsService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicPluginsService = new Schematic_PluginsService();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../SchematicPlugin.php';
        require_once __DIR__.'/../models/Schematic_ResultModel.php';
        require_once __DIR__.'/../services/Schematic_AbstractService.php';
        require_once __DIR__.'/../services/Schematic_PluginsService.php';
    }

    /**
     * @param bool $returnPlugin
     * @param bool $installPluginResponse
     * @param bool $enablePluginResponse
     * @param bool $disablePluginResponse
     * @param bool $uninstallPluginResponse
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

        if($installPluginResponse) {
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
     * @return Mock|BasePlugin
     */
    public function getMockBasePlugin()
    {
        $mock = $this->getMockBuilder(BasePlugin::class)->getMock();

        return $mock;
    }

    /**
     * Test default import functionality
     * @covers ::import
     */
    public function testImportWithInstalledPlugins()
    {
        $data = $this->getPluginsData();
        $mockPluginsService = $this->getMockPluginsService();
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality
     * @covers ::import
     */
    public function testImportWithInstalledDisabledPlugins()
    {
        $data = $this->getPluginsData();
        $data['itmundiplugin']['isEnabled'] = false;
        $mockPluginsService = $this->getMockPluginsService();
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality
     * @covers ::import
     */
    public function testImportWithMissingPlugin()
    {
        $data = $this->getPluginsData();

        $mockPluginsService = $this->getMockPluginsService(false);
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test default import functionality
     * @covers ::import
     */
    public function testImportWithInstallException()
    {
        $data = $this->getPluginsData();

        $mockPluginsService = $this->getMockPluginsService(true, false);
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test default import functionality
     * @covers ::import
     */
    public function testImportWithNotInstalledPlugin()
    {
        $mockPluginsService = $this->getMockPluginsService();
        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $data = $this->getPluginsData();
        $data['itmundiplugin']['isInstalled'] = false;

        $import = $this->schematicPluginsService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test export functionality
     * @covers ::export
     */
    public function testExport()
    {
        $data = $this->getPluginsData();

        $mockBasePlugin = $this->getMockBasePlugin();
        $mockBasePlugin->isInstalled = true;
        $mockBasePlugin->isEnabled = true;
        $mockBasePlugin
            ->expects($this->any())
            ->method('getSettings')
            ->willReturn((Object) array('attributes' => $data['itmundiplugin']['settings']));

        $mockPluginsService = $this->getMockPluginsService();
        $mockPluginsService->expects($this->any())
            ->method('getPlugins')
            ->willReturn(array('itmundiplugin' => $mockBasePlugin));

        $this->setComponent(craft(), 'plugins', $mockPluginsService);

        $export = $this->schematicPluginsService->export();
        $this->assertEquals($data, $export);
    }

    /**
     * Returns plugins data
     * @return array
     */
    public function getPluginsData()
    {
        return array(
            'itmundiplugin'         => array(
                'isInstalled'       => true,
                'isEnabled'         => true,
                'settings'          => array(
                    'pluginName'    => 'Menu',
                    'canDoActions'  => '',
                    'quietErrors'   => ''
                )
            )
        );
    }
}
