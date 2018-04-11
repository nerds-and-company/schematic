<?php

namespace Helper;

use Craft;
use craft\console\Application;
use yii\console\Controller;
use craft\i18n\I18n;
use craft\services\AssetTransforms;
use craft\services\Categories;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Globals;
use craft\services\Matrix;
use craft\services\Path;
use craft\services\Sections;
use craft\services\Sites;
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
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        $mockApp = $this->getMockApp($test);
        $mockApp->controller = $this->getMock($test, Controller::class);
        $mockApp->controller->module = $this->getmockModule($test);

        Craft::$app = $mockApp;
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
        $mockAssetTransforms = $this->getMock($test, AssetTransforms::class);
        $mockCategoryGroups = $this->getMock($test, Categories::class);
        $mockElements = $this->getMock($test, Elements::class);
        $mockFields = $this->getMock($test, Fields::class);
        $mockGlobals = $this->getMock($test, Globals::class);
        $mockI18n = $this->getMock($test, I18n::class);
        $mockMatrix = $this->getMock($test, Matrix::class);
        $mockPath = $this->getMock($test, Path::class);
        $mockSections = $this->getMock($test, Sections::class);
        $mockSites = $this->getMock($test, Sites::class);
        $mockTags = $this->getMock($test, Tags::class);
        $mockUserGroups = $this->getMock($test, UserGroups::class);
        $mockUserPermissions = $this->getMock($test, UserPermissions::class);
        $mockVolumes = $this->getMock($test, Volumes::class);

        $mockApp->expects($test->any())
            ->method('__get')
            ->willReturnMap([
                ['assetTransforms', $mockAssetTransforms],
                ['categories', $mockCategoryGroups],
                ['elements', $mockElements],
                ['fields', $mockFields],
                ['globals', $mockGlobals],
                ['matrix', $mockMatrix],
                ['sections', $mockSections],
                ['sites', $mockSites],
                ['tags', $mockTags],
                ['userGroups', $mockUserGroups],
                ['userPermissions', $mockUserPermissions],
                ['volumes', $mockVolumes],
            ]);

        $mockApp->expects($test->any())
                ->method('getPath')
                ->willreturn($mockPath);

        $mockApp->expects($test->any())
                ->method('getI18n')
                ->willReturn($mockI18n);

        $mockApp->expects($test->any())
                ->method('getMatrix')
                ->willreturn($mockMatrix);

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
