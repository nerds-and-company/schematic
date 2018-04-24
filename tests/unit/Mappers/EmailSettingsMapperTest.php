<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use Codeception\Test\Unit;

/**
 * Class EmailSettingsMapperTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class EmailSettingsMapperTest extends Unit
{
    /**
     * @var EmailSettingsMapper
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
        $this->mapper = new EmailSettingsMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Test EmailSettings service export.
     */
    public function testEmailSettingsServiceExport()
    {
        $this->setMockServicesForExport();

        $definition = $this->getEmailSettingsDefinition();
        $result = $this->mapper->export();

        $this->assertSame($definition, $result);
    }

    /**
     * Test EmailSettings service import.
     */
    public function testEmailSettingsServiceImport()
    {
        $definition = $this->getEmailSettingsDefinition();
        $import = $this->mapper->import($definition);

        $this->assertSame([], $import);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * Email settings definition.
     *
     * @return array
     */
    private function getEmailSettingsDefinition()
    {
        return [
            'settings' => [
                'fromEmail' => 'admin@example.com',
                'fromName' => 'Schematic',
                'template' => null,
            ],
        ];
    }

    /**
     * Set mock services for export.
     */
    private function setMockServicesForExport()
    {
        $settings = [
            'fromEmail' => 'admin@example.com',
            'fromName' => 'Schematic',
            'template' => null,
        ];

        Craft::$app->systemSettings->expects($this->exactly(1))
                                   ->method('getSettings')
                                   ->with('email')
                                   ->willReturn($settings);
    }
}
