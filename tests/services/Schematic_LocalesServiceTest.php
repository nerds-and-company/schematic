<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_LocalesServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass Craft\Schematic_LocalesService
 * @covers ::__construct
 * @covers ::<!public>
 */
class Schematic_LocalesServiceTest extends BaseTest
{
    /**
     * @var Schematic_LocalesService
     */
    private $schematicLocalesService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicLocalesService = new Schematic_LocalesService();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../models/Schematic_ResultModel.php';
        require_once __DIR__.'/../../services/Schematic_AbstractService.php';
        require_once __DIR__.'/../../services/Schematic_LocalesService.php';
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
        $getSiteLocaleIdsResponse = array(),
        $getSiteLocalesResponse = array(),
        $addSiteLocaleResponse = true)
    {
        $mock = $this->getMockBuilder('Craft\LocalizationService')->getMock();
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
        $this->setComponent(craft(), 'i18n', $mockLocalizationService);

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
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
        $this->setComponent(craft(), 'i18n', $mockLocalizationService);

        $data = $this->getLocaleData();

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImportWithFailedInstalledLocales()
    {
        $mockLocalizationService = $this->getMockLocalizationService(array(), array(), false);
        $this->setComponent(craft(), 'i18n', $mockLocalizationService);

        $data = $this->getLocaleData();

        $import = $this->schematicLocalesService->import($data);

        $this->assertTrue($import instanceof Schematic_ResultModel);
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

        $locales = array();
        foreach ($data as $id) {
            $locales[] = new LocaleModel($id);
        }

        $mockLocalizationService = $this->getMockLocalizationService($data, $locales);

        $this->setComponent(craft(), 'i18n', $mockLocalizationService);

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
        return array('nl', 'en', 'de');
    }
}
