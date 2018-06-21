<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use Codeception\Test\Unit;
use craft\models\Info;

/**
 * Class GeneralSettingsMapperTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GeneralSettingsMapperTest extends Unit
{
    /**
     * @var GeneralSettingsMapper
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
        $this->mapper = new GeneralSettingsMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Test GeneralSettings service export.
     */
    public function testGeneralSettingsServiceExport()
    {
        $this->setMockServicesForExport();

        $definition = $this->getGeneralSettingsDefinition();
        $result = $this->mapper->export();

        $this->assertSame($definition, $result);
    }

    /**
     * Test GeneralSettings service import.
     */
    public function testGeneralSettingsServiceImport()
    {
        $definition = $this->getGeneralSettingsDefinition();
        $import = $this->mapper->import($definition);

        $this->assertSame([], $import);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * General settings definition.
     *
     * @return array
     */
    private function getGeneralSettingsDefinition()
    {
        return [
            'settings' => [
                'edition' => Craft::Pro,
                'timezone' => 'Europe/Amsterdam',
                'name' => 'Schematic',
                'on' => true,
                'maintenance' => false,
            ],
        ];
    }

    /**
     * Set mock services for export.
     */
    private function setMockServicesForExport()
    {
        $mockInfoModel = $this->getMockInfoModel();

        Craft::$app->expects($this->exactly(1))
                   ->method('getInfo')
                   ->willReturn($mockInfoModel);
    }

    /**
     * Get a mock info model.
     *
     * @return Mock|Info
     */
    private function getMockInfoModel(): Info
    {
        $mockModel = $this->getMockBuilder(Info::class)->getMock();
        $mockModel->edition = Craft::Pro;
        $mockModel->timezone = 'Europe/Amsterdam';
        $mockModel->name = 'Schematic';
        $mockModel->on = true;
        $mockModel->maintenance = false;

        return $mockModel;
    }
}
