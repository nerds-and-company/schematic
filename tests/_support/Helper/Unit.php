<?php

namespace Helper;

use Craft;
use craft\console\Application;
use craft\services\Categories;
use craft\services\Elements;
use craft\services\Globals;
use craft\services\Fields;
use craft\services\Sites;
use craft\i18n\I18n;
use Codeception\Module;
use Codeception\TestCase;
use NerdsAndCompany\Schematic\Schematic;

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
        $mockvolumes = $this->getMock($test, Volumes::class);
        $mockCategoryGroups = $this->getMock($test, Categories::class);
        $mockElements = $this->getMock($test, Elements::class);
        $mockFields = $this->getMock($test, Fields::class);
        $mockGlobals = $this->getMock($test, Globals::class);
        $mockSites = $this->getMock($test, Sites::class);
        $mockI18n = $this->getMock($test, I18n::class);

        $mockApp->expects($test->any())
            ->method('__get')
            ->willReturnMap([
                ['categories', $mockCategoryGroups],
                ['elements', $mockElements],
                ['globals', $mockGlobals],
                ['fields', $mockFields],
                ['sites', $mockSites],
            ]);

        $mockApp->expects($test->any())
            ->method('getI18n')
            ->willReturn($mockI18n);

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
