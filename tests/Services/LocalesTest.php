<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\LocaleModel;
use Craft\LocalizationService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class LocalesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\Locales
 * @covers ::__construct
 * @covers ::<!public>
 */
class LocalesTest extends BaseTest
{
    /**
     * @var Locales
     */
    private $schematicLocalesService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicLocalesService = new Locales();
    }

    /**
     * @return LocalizationService|Mock
     *
     * @param array $getSiteLocalesResponse
     * @param bool  $addSiteLocaleResponse
     *
     * @return Mock
     */
    protected function getMockLocalizationService(
        $getSiteLocaleIdsResponse = [],
        $getSiteLocalesResponse = [],
        $addSiteLocaleResponse = true
    ) {
        $mock = $this->getMockBuilder(LocalizationService::class)->getMock();
        $mock->expects($this->any())->method('getSiteLocaleIds')->willReturn($getSiteLocaleIdsResponse);
        $mock->expects($this->any())->method('getSiteLocales')->willReturn($getSiteLocalesResponse);
        $mock->expects($this->any())->method('addSiteLocale')->willReturn($addSiteLocaleResponse);
        $mock->expects($this->any())->method('reorderSiteLocales')->willReturn(true);

        return $mock;
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithInstalledLocales()
    {
        $data = $this->getLocaleData();

        $mockLocalizationService = $this->getMockLocalizationService($data);
        $this->setComponent(Craft::app(), 'i18n', $mockLocalizationService);

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithNotInstalledLocales()
    {
        $mockLocalizationService = $this->getMockLocalizationService();
        $this->setComponent(Craft::app(), 'i18n', $mockLocalizationService);

        $data = $this->getLocaleData();

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithFailedInstalledLocales()
    {
        $mockLocalizationService = $this->getMockLocalizationService([], [], false);
        $this->setComponent(Craft::app(), 'i18n', $mockLocalizationService);

        $data = $this->getLocaleData();

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test export functionality.
     *
     * @covers ::export
     */
    public function testExport()
    {
        $data = $this->getLocaleData();

        $locales = [];
        foreach ($data as $id) {
            $locales[] = new LocaleModel($id);
        }

        $mockLocalizationService = $this->getMockLocalizationService($data, $locales);

        $this->setComponent(Craft::app(), 'i18n', $mockLocalizationService);

        $export = $this->schematicLocalesService->export();
        $this->assertEquals($data, $export);
    }

    /**
     * Returns locale data.
     *
     * @return array
     */
    public function getLocaleData()
    {
        return ['nl', 'en', 'de'];
    }
}
