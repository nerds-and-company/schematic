<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Field as FieldModel;
use craft\fields\Matrix as MatrixField;
use craft\models\MatrixBlockType as MatrixBlockTypeModel;
use craft\models\FieldLayout;
use Codeception\Test\Unit;

/**
 * Class MatrixBlockTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class MatrixBlockTypeTest extends Unit
{
    /**
     * @var MatrixBlockType
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->converter = new MatrixBlockType();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideMatrixBlockTypes
     *
     * @param MatrixBlockTypeModel $blockType
     * @param array                $definition
     */
    public function testGetRecordDefinition(MatrixBlockTypeModel $blockType, array $definition)
    {
        Craft::$app->controller->module->modelMapper->expects($this->exactly(1))
                                     ->method('export')
                                     ->with($blockType->getFieldLayout()->getFields())
                                     ->willReturn($definition['fields']);

        $result = $this->converter->getRecordDefinition($blockType);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideMatrixBlockTypes
     *
     * @param MatrixBlockTypeModel $blockType
     * @param array                $definition
     * @param array                $defaultAttributes
     */
    public function testSetRecordAttributes(
        MatrixBlockTypeModel $blockType,
        array $definition,
        array $defaultAttributes
    ) {
        $newMatrixBlockType = $this->getMockBuilder(MatrixBlockTypeModel::class)
                                   ->setMethods(['setFieldLayout'])
                                   ->getMock();

        $newMatrixBlockType->expects($this->exactly(0))
                           ->method('setFieldLayout');

        $this->converter->setRecordAttributes($newMatrixBlockType, $definition, $defaultAttributes);

        $this->assertSame($defaultAttributes['fieldId'], $newMatrixBlockType->fieldId);
        $this->assertSame($blockType->name, $newMatrixBlockType->name);
        $this->assertSame($blockType->handle, $newMatrixBlockType->handle);
    }

    /**
     * @dataProvider provideMatrixBlockTypes
     *
     * @param MatrixBlockTypeModel $blockType
     * @param array                $definition
     */
    public function testSaveRecord(MatrixBlockTypeModel $blockType, array $definition)
    {
        Craft::$app->fields->expects($this->exactly(1))
                           ->method('getFieldById')
                           ->with(1)
                           ->willReturn($this->getMockbuilder(MatrixField::class)->getMock());

        Craft::$app->getFields()->expects($this->exactly(1))
                                ->method('getFieldsByLayoutId')
                                ->willReturn([$this->getMockField(1)]);

        Craft::$app->matrix->expects($this->exactly(1))
                           ->method('getContentTableName')
                           ->willReturn('matrix_content');

        Craft::$app->matrix->expects($this->exactly(1))
                           ->method('saveBlockType')
                           ->with($blockType, false)
                           ->willReturn(true);

        $existingFields = $blockType->getFieldLayout()->getFields();

        Craft::$app->controller->module->modelMapper->expects($this->exactly(1))
                                     ->method('import')
                                     ->with($definition['fields'], $existingFields, [], false)
                                     ->willReturn($existingFields);

        $result = $this->converter->saveRecord($blockType, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideMatrixBlockTypes
     *
     * @param MatrixBlockTypeModel $blockType
     */
    public function testDeleteRecord(MatrixBlockTypeModel $blockType)
    {
        Craft::$app->matrix->expects($this->exactly(1))
                           ->method('deleteBlockType')
                           ->with($blockType);

        $this->converter->deleteRecord($blockType);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideMatrixBlockTypes()
    {
        $mockMatrixBlockType = $this->getMockMatrixBlockType(1);

        return [
            'valid blockType' => [
                'blockType' => $mockMatrixBlockType,
                'definition' => $this->getMockMatrixBlockTypeDefinition($mockMatrixBlockType),
                'defaultAttributes' => ['fieldId' => 1],
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param MatrixBlockTypeModel $mockMatrixBlockType
     *
     * @return array
     */
    private function getMockMatrixBlockTypeDefinition(MatrixBlockTypeModel $mockMatrixBlockType)
    {
        return [
            'class' => get_class($mockMatrixBlockType),
            'attributes' => [
                'name' => 'blockTypeName'.$mockMatrixBlockType->id,
                'handle' => 'blockTypeHandle'.$mockMatrixBlockType->id,
                'sortOrder' => null,
            ],
            'fields' => [
                'fieldDefinition',
            ],
        ];
    }

    /**
     * @param int $blockTypeId
     *
     * @return Mock|MatrixBlockTypeModel
     */
    private function getMockMatrixBlockType(int $blockTypeId)
    {
        $mockMatrixBlockType = $this->getMockBuilder(MatrixBlockTypeModel::class)
                              ->setMethods(['getFieldLayout'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $mockMatrixBlockType->id = $blockTypeId;
        $mockMatrixBlockType->fieldId = 1;
        $mockMatrixBlockType->fieldLayoutId = $blockTypeId;
        $mockMatrixBlockType->handle = 'blockTypeHandle'.$blockTypeId;
        $mockMatrixBlockType->name = 'blockTypeName'.$blockTypeId;

        $mockField = $this->getMockField($blockTypeId);

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();

        $mockFieldLayout->expects($this->any())
                        ->method('getFields')
                        ->willReturn([$mockField]);

        $mockMatrixBlockType->expects($this->any())
                   ->method('getFieldLayout')
                   ->willReturn($mockFieldLayout);

        return $mockMatrixBlockType;
    }

    /**
     * Get a mock field.
     *
     * @param int $fieldId
     *
     * @return Mock|FieldModel
     */
    private function getMockField(int $fieldId)
    {
        $mockField = $this->getMockbuilder(FieldModel::class)
                         ->setMethods([])
                         ->getMock();

        $mockField->id = $fieldId;
        $mockField->handle = 'field'.$fieldId;
        $mockField->required = true;

        return $mockField;
    }
}
