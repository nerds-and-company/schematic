<?php

namespace Helper;

use Craft;
use craft\console\Application;
use craft\services\Categories;
use craft\services\Fields;
use craft\services\Sites;
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
        $mockCategoryGroups = $test->getMockBuilder(Categories::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $mockFields = $test->getMockBuilder(Fields::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $mockSites = $test->getMockBuilder(Sites::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $mockApp = $test->getMockBuilder(Application::class)
                  ->disableOriginalConstructor()
                  ->getMock();

        $mockApp->expects($test->any())
                ->method('__get')
                ->willReturnMap([
                    ['categories', $mockCategoryGroups],
                    ['fields', $mockFields],
                    ['sites', $mockSites],
                ]);

        Craft::$app = $mockApp;
        Schematic::$force = false;
    }
}
