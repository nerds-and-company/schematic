<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use Exception;
use craft\base\Model;
use craft\base\Plugin;
use NerdsAndCompany\Schematic\Schematic;
use Codeception\Test\Unit;

/**
 * Class Plugin Mapper Test.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PluginMapperTest extends Unit
{
    /**
     * @var PluginMapper
     */
    private $mapper;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->mapper = new PluginMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Test default import functionality.
     */
    public function testImportWithInstalledPlugins()
    {
        $definitions = $this->getPluginsDefinition();
        $data = $this->getPluginsData();

        $this->mockPluginsService();

        $result = $this->mapper->import($definitions, $data);

        $this->assertCount(count($definitions), $result);
    }

    /**
     * Test default import functionality.
     */
    public function testImportWithInstalledDisabledPlugins()
    {
        $this->getMockplugin();

        $definitions = $this->getPluginsDefinition();
        $definitions['pluginHandle']['isEnabled'] = false;
        $data = $this->getPluginsData();

        $this->mockPluginsService();

        $result = $this->mapper->import($definitions, $data);

        $this->assertCount(count($definitions), $result);
    }

    /**
     * Test default import functionality.
     */
    public function testImportWithMissingPlugin()
    {
        $definitions = $this->getPluginsDefinition();

        $this->mockPluginsService(false);

        $result = $this->mapper->import($definitions, []);

        $this->assertCount(0, $result);
    }

    /**
     * Test default import functionality.
     */
    public function testImportWithInstallException()
    {
        $definitions = $this->getPluginsDefinition();
        $data = $this->getPluginsData();

        $this->mockPluginsService(true, false);

        $this->expectException(Exception::class);

        $this->mapper->import($definitions, $data);
    }

    /**
     * Test default import functionality.
     */
    public function testImportWithNotInstalledPlugin()
    {
        $this->getMockplugin();

        $definitions = $this->getPluginsDefinition();
        $definitions['pluginHandle']['isInstalled'] = false;
        $data = $this->getPluginsData();

        $this->mockPluginsService();

        $result = $this->mapper->import($definitions, $data);

        $this->assertCount(0, $result);
    }

    /**
     * Test default import functionality.
     */
    public function testImportWithForce()
    {
        $data = $this->getPluginsData();
        $data['pluginHandle']['isInstalled'] = true;

        $this->mockPluginsService(false);

        Craft::$app->plugins->expects($this->exactly(1))
                            ->method('uninstallPlugin')
                            ->willReturn(true);

        Schematic::$force = true;
        $result = $this->mapper->import([], $data);

        $this->assertCount(0, $result);
    }

    /**
     * Test export functionality.
     */
    public function testExport()
    {
        $data = $this->getPluginsData();
        $data['pluginHandle']['isInstalled'] = true;
        $data['pluginHandle']['isEnabled'] = true;

        $definitions = $this->getPluginsDefinition();
        $this->mockPluginsService();

        $result = $this->mapper->export($data);

        $this->assertEquals($definitions, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param bool $returnPlugin
     * @param bool $installPluginResponse
     * @param bool $enablePluginResponse
     * @param bool $disablePluginResponse
     * @param bool $saveSettingsResponse
     */
    private function mockPluginsService(
        $returnPlugin = true,
        $installPluginResponse = true,
        $enablePluginResponse = true,
        $disablePluginResponse = true,
        $saveSettingsResponse = true
    ) {
        Craft::$app->plugins->expects($this->any())
                            ->method('getPlugin')
                            ->willReturn(($returnPlugin) ? $this->getMockplugin() : null);

        if ($installPluginResponse) {
            Craft::$app->plugins->expects($this->any())->method('installPlugin')->willReturn($installPluginResponse);
        } else {
            Craft::$app->plugins->expects($this->any())->method('installPlugin')->willThrowException(new Exception());
        }

        Craft::$app->plugins->expects($this->any())->method('enablePlugin')->willReturn($enablePluginResponse);
        Craft::$app->plugins->expects($this->any())->method('disablePlugin')->willReturn($disablePluginResponse);
        Craft::$app->plugins->expects($this->any())->method('savePluginSettings')->willReturn($saveSettingsResponse);
    }

    /**
     * @return Mock|plugin
     */
    private function getMockplugin()
    {
        $mockPlugin = $this->getMockBuilder(Plugin::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockPlugin->expects($this->any())
                   ->method('getSettings')
                   ->willReturn($this->getMockSettings());

        return $mockPlugin;
    }

    /**
     * Get mock settings.
     *
     * @return Mock|Model
     */
    private function getMockSettings()
    {
        $mockSettings = $this->getMockBuilder(Model::class)->getMock();
        $mockSettings->expects($this->any())
                     ->method('__get')
                     ->willReturnMap([
                         ['attributes', [
                             'pluginName' => 'Menu',
                             'canDoActions' => '',
                             'quietErrors' => '',
                          ]],
                      ]);

        return $mockSettings;
    }

    /**
     * Returns plugins data.
     *
     * @return array
     */
    private function getPluginsData()
    {
        return [
            'pluginHandle' => [
                'isInstalled' => false,
                'isEnabled' => false,
                'settings' => [
                    'pluginName' => 'Menu',
                    'canDoActions' => '',
                    'quietErrors' => '',
                ],
            ],
        ];
    }

    /**
     * Returns plugins data.
     *
     * @return array
     */
    private function getPluginsDefinition()
    {
        return [
            'pluginHandle' => [
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
