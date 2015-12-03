<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_UsersServiceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass Craft\Schematic_UsersService
 * @covers ::__construct
 * @covers ::<!public>
 */
class Schematic_UsersServiceTest extends BaseTest
{
    /**
     * @var Schematic_UsersService
     */
    private $schematicUsersService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicUsersService = new Schematic_UsersService();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../models/Schematic_ResultModel.php';
        require_once __DIR__.'/../../services/Schematic_AbstractService.php';
        require_once __DIR__.'/../../services/Schematic_UsersService.php';
        require_once __DIR__.'/../../services/Schematic_FieldsService.php';
    }

    /**
     * Get mock for localization service.
     *
     * @return Mock|PathService
     */
    public function getMockLocalizationService()
    {
        $mock = $this->getMockBuilder('Craft\LocalizationService')->getMock();

        $mock->expects($this->any())->method('getPrimarySiteLocaleId')->willReturn('nl_nl');

        return $mock;
    }

    /**
     * @return Mock|FieldLayoutModel
     */
    public function getMockFieldLayout()
    {
        $mock = $this->getMockBuilder('Craft\FieldLayoutModel')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return Mock|Schematic_FieldsService
     */
    public function getMockSchematicFieldsService()
    {
        $mock = $this->getMockBuilder('Craft\Schematic_FieldsService')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return FieldsService|Mock
     */
    public function getMockFieldsService()
    {
        $mock = $this->getMockBuilder('Craft\FieldsService')->getMock();

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
        $mockSchematicFieldsService->expects($this->exactly(1))->method('getFieldLayoutDefinition')->willReturn(array());

        $this->setComponent(craft(), 'i18n', $mockI18n);
        $this->setComponent(craft(), 'fields', $mockFieldsService);
        $this->setComponent(craft(), 'schematic_fields', $mockSchematicFieldsService);

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
     * @return Schematic_FieldsService|Mock
     */
    private function getMockSchematicFieldServiceForImport($errors = false)
    {
        $mockFieldLayout = $this->getMockFieldLayout();
        if ($errors) {
            $mockFieldLayout->expects($this->exactly(1))->method('getAllErrors')->willReturn(array(
                'errors' => array('error 1', 'error 2', 'error 3'),
            ));
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

        $this->setComponent(craft(), 'fields', $mockFieldsService);
        $this->setComponent(craft(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import(array('fieldLayout' => array()));

        $this->assertTrue($import instanceof Schematic_ResultModel);
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

        $this->setComponent(craft(), 'fields', $mockFieldsService);
        $this->setComponent(craft(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import(array());

        $this->assertTrue($import instanceof Schematic_ResultModel);
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

        $this->setComponent(craft(), 'fields', $mockFieldsService);
        $this->setComponent(craft(), 'schematic_fields', $mockSchematicFieldsService);

        $import = $this->schematicUsersService->import(array());

        $this->assertTrue($import instanceof Schematic_ResultModel);
        $this->assertTrue($import->hasErrors('errors'));
    }
}
