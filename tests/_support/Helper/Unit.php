<?php

namespace Helper;

use Craft;
use craft\console\Application;
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
use craft\services\Volumes;
use Codeception\Module;
use Codeception\TestCase;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Services\ModelProcessor;

/**
 * UnitTest helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Unit extends Module
{
    /**
     * Mock craft services.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        $mockApp = $this->getMock($test, Application::class);
        $mockAssetTransforms = $this->getMock($test, AssetTransforms::class);
        $mockCategoryGroups = $this->getMock($test, Categories::class);
        $mockElements = $this->getMock($test, Elements::class);
        $mockFields = $this->getMock($test, Fields::class);
        $mockGlobals = $this->getMock($test, Globals::class);
        $mockI18n = $this->getMock($test, I18n::class);
        $mockMatrix = $this->getMock($test, Matrix::class);
        $mockModelProcessor = $this->getMock($test, ModelProcessor::class);
        $mockPath = $this->getMock($test, Path::class);
        $mockSections = $this->getMock($test, Sections::class);
        $mockSites = $this->getMock($test, Sites::class);
        $mockvolumes = $this->getMock($test, Volumes::class);

        $mockApp->expects($test->any())
            ->method('__get')
            ->willReturnMap([
                ['assetTransforms', $mockAssetTransforms],
                ['categories', $mockCategoryGroups],
                ['elements', $mockElements],
                ['fields', $mockFields],
                ['globals', $mockGlobals],
                ['matrix', $mockMatrix],
                ['schematic_fields', $mockModelProcessor],
                ['schematic_sections', $mockModelProcessor],
                ['sections', $mockSections],
                ['sites', $mockSites],
                ['volumes', $mockvolumes],
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

        Craft::$app = $mockApp;
        Schematic::$force = false;
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
