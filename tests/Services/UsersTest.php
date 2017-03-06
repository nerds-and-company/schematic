<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\FieldLayoutModel;
use Craft\LocalizationService;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class UsersTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\Users
 * @covers ::__construct
 * @covers ::<!public>
 */
class UsersTest extends BaseTest
{
    /**
     * @var Users
     */
    private $schematicUsersService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicUsersService = new Users();
    }

    /**
     * Get mock for localization service.
     *
     * @return Mock|PathService
     */
    public function getMockLocalizationService()
    {
        $mock = $this->getMockBuilder(LocalizationService::class)->getMock();

        $mock->expects($this->any())->method('getPrimarySiteLocaleId')->willReturn('nl_nl');

        return $mock;
    }

    /**
     * @return Mock|FieldLayoutModel
     */
    public function getMockFieldLayout()
    {
        $mock = $this->getMockBuilder(FieldLayoutModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return Mock|Fields
     */
    public function getMockSchematicFieldsService()
    {
        $mock = $this->getMockBuilder(Fields::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return FieldsService|Mock
     */
    public function getMockFieldsService()
    {
        $mock = $this->getMockBuilder(FieldsService::class)->getMock();

        return $mock;
    }

    /**
     * Test users service export.
     *
     * @covers ::export
     */
    public function testUsersServiceExport()
    {
        $mockI18n = $this->getMockLocalizationService();

        $mockFieldLayout = $this->getMockFieldLayout();

        $mockFieldsService = $this->getMockFieldsService();
        $mockFieldsService->expects($this->exactly(1))->method('getLayoutByType')->willReturn($mockFieldLayout);

        $mockSchematicFieldsService = $this->getMockSchematicFieldsService();
        $mockSchematicFieldsService->expects($this->exactly(1))->method('getFieldLayoutDefinition')->willReturn([]);

        $this->setComponent(Craft::app(), 'i18n', $mockI18n);
        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);
        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFieldsService);

        $export = $this->schematicUsersService->export();

        $this->assertArrayHasKey('fieldLayout', $export);
    }

    /**
     * @param bool $safeLayout
     * @param bool $deleteLayoutsByType
     *
     * @return FieldsService|Mock
     */
    private function getMockFieldServiceForImport($safeLayout = true, $deleteLayoutsByType = true)
    {
        $mockFieldsService = $this->getMockFieldsService();
        $mockFieldsService->expects($this->exactly(1))->method('saveLayout')->willReturn($safeLayout);
        $mockFieldsService->expects($this->exactly(1))->method('deleteLayoutsByType')->willReturn($deleteLayoutsByType);

        return  $mockFieldsService;
    }

    /**
     * @param bool $errors
     *
     * @return Fields|Mock
     */
    private function getMockSchematicFieldServiceForImport($errors = false)
    {
        $mockFieldLayout = $this->getMockFieldLayout();
        if ($errors) {
            $mockFieldLayout->expects($this->exactly(1))->method('getAllErrors')->willReturn([
                'errors' => ['error 1', 'error 2', 'error 3'],
            ]);
        }

        $mockSchematicFieldsService = $this->getMockSchematicFieldsService();
        $mockSchematicFieldsService->expects($this->exactly(1))->method('getFieldLayout')->willReturn($mockFieldLayout);

        return $mockSchematicFieldsService;
    }

    /**
     * Test users service import.
     *
     * @covers ::import
     */
    public function testUsersServiceImportWithForce()
    {
        $mockFieldsService = $this->getMockFieldServiceForImport();
        $mockSchematicFieldsService = $this->getMockSchematicFieldServiceForImport();

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);
        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import(['fieldLayout' => []]);

        $this->assertTrue($import instanceof Result);
    }

    /**
     * Test users service import.
     *
     * @covers ::import
     */
    public function testUsersServiceImportWithoutFieldLayout()
    {
        $mockFieldsService = $this->getMockFieldServiceForImport();
        $mockSchematicFieldsService = $this->getMockSchematicFieldServiceForImport();

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);
        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import([]);

        $this->assertTrue($import instanceof Result);
    }

    /**
     * Test users service import.
     *
     * @covers ::import
     */
    public function testUsersServiceImportWithImportError()
    {
        $mockFieldsService = $this->getMockFieldServiceForImport(false);
        $mockSchematicFieldsService = $this->getMockSchematicFieldServiceForImport(true);

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);
        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import([]);

        $this->assertTrue($import instanceof Result);
        $this->assertTrue($import->hasErrors('errors'));
    }
}
