<?php

namespace Helper;

use Craft;
use Yii;
use craft\console\Application;
use yii\console\Controller;
use craft\i18n\I18n;
use craft\services\Assets;
use craft\services\AssetTransforms;
use craft\services\Categories;
use craft\services\Content;
use craft\services\Elements;
use craft\services\ElementIndexes;
use craft\services\Fields;
use craft\services\Globals;
use craft\services\Matrix;
use craft\services\Path;
use craft\services\Plugins;
use craft\services\Sections;
use craft\services\Sites;
use craft\services\SystemSettings;
use craft\services\Tags;
use craft\services\UserGroups;
use craft\services\UserPermissions;
use craft\services\Volumes;
use Codeception\Module;
use Codeception\TestCase;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Mappers\ModelMapper;

/**
 * UnitTest helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Unit extends Module
{
    /**
     * Mock craft Mappers.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        $mockApp = $this->getMockApp($test);
        $mockApp->controller = $this->getMock($test, Controller::class);
        $mockApp->controller->module = $this->getMockModule($test);

        Craft::$app = $mockApp;
        Yii::$app = $mockApp;
        Schematic::$force = false;
    }

    /**
     * Get a preconfigured mock module.
     *
     * @param TestCase $test
     *
     * @return Mock|Schematic
     */
    private function getMockModule(TestCase $test)
    {
        $mockModule = $this->getMock($test, Schematic::class);
        $mockModelMapper = $this->getMock($test, ModelMapper::class);
        $mockModule->expects($test->any())
                   ->method('__get')
                   ->willReturnMap([
                        ['modelMapper', $mockModelMapper],
                    ]);

        return $mockModule;
    }

    /**
     * Get a preconfigured mock app.
     *
     * @param TestCase $test
     *
     * @return Mock|Application
     */
    private function getMockApp(TestCase $test)
    {
        $mockApp = $this->getMock($test, Application::class);
        $mockAssets = $this->getMock($test, Assets::class);
        $mockAssetTransforms = $this->getMock($test, AssetTransforms::class);
        $mockCategoryGroups = $this->getMock($test, Categories::class);
        $mockContent = $this->getMock($test, Content::class);
        $mockElements = $this->getMock($test, Elements::class);
        $mockElementIndexes = $this->getMock($test, ElementIndexes::class);
        $mockFields = $this->getMock($test, Fields::class);
        $mockGlobals = $this->getMock($test, Globals::class);
        $mockI18n = $this->getMock($test, I18n::class);
        $mockMatrix = $this->getMock($test, Matrix::class);
        $mockPath = $this->getMock($test, Path::class);
        $mockPlugins = $this->getMock($test, Plugins::class);
        $mockSections = $this->getMock($test, Sections::class);
        $mockSites = $this->getMock($test, Sites::class);
        $mockSystemSettings = $this->getMock($test, SystemSettings::class);
        $mockTags = $this->getMock($test, Tags::class);
        $mockUserGroups = $this->getMock($test, UserGroups::class);
        $mockUserPermissions = $this->getMock($test, UserPermissions::class);
        $mockVolumes = $this->getMock($test, Volumes::class);

        $mockApp->expects($test->any())
            ->method('__get')
            ->willReturnMap([
                ['assets', $mockAssets],
                ['assetTransforms', $mockAssetTransforms],
                ['categories', $mockCategoryGroups],
                ['content', $mockContent],
                ['elements', $mockElements],
                ['elementIndexes', $mockElementIndexes],
                ['fields', $mockFields],
                ['globals', $mockGlobals],
                ['matrix', $mockMatrix],
                ['plugins', $mockPlugins],
                ['sections', $mockSections],
                ['sites', $mockSites],
                ['systemSettings', $mockSystemSettings],
                ['tags', $mockTags],
                ['userGroups', $mockUserGroups],
                ['userPermissions', $mockUserPermissions],
                ['volumes', $mockVolumes],
            ]);

        $mockApp->expects($test->any())
                ->method('getPath')
                ->willReturn($mockPath);

        $mockApp->expects($test->any())
                ->method('getI18n')
                ->willReturn($mockI18n);

        $mockApp->expects($test->any())
                ->method('getMatrix')
                ->willReturn($mockMatrix);

        $mockApp->expects($test->any())
                ->method('getFields')
                ->willReturn($mockFields);

        return $mockApp;
    }

    /**
     * Get a mock object for class.
     *
     * @param TestCase $test
     * @param string   $class
     *
     * @return Mock
     */
    private function getMock(TestCase $test, string $class)
    {
        return $test->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();
    }
}
