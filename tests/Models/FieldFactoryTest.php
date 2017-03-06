<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\Craft;
use Craft\BaseTest;
use Craft\ColorFieldType;
use Craft\PluginsService;

/**
 * Class FieldFactoryTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Models\FieldFactory
 * @covers ::<!public>
 */
class FieldFactoryTest extends BaseTest
{
    /**
     * @covers ::build
     * @dataProvider provideFieldTypes
     *
     * @param string $fieldType
     * @param string $expectedClassName
     */
    public function testBuildWithDefaultFieldTypesReturnsCorrectClass($fieldType, $expectedClassName)
    {
        $fieldFactory = new FieldFactory();
        $schematicFieldModel = $fieldFactory->build($fieldType);

        $this->assertEquals($expectedClassName, get_class($schematicFieldModel));
    }

    /**
     * @covers ::build
     * @dataProvider provideHookedFieldTypes
     *
     * @param string $fieldType
     * @param string $expectedClassName
     * @param array  $hookCallResults
     */
    public function testBuildWithFieldHook($fieldType, $expectedClassName, array $hookCallResults)
    {
        $this->setMockPluginsService($hookCallResults);

        $fieldFactory = new FieldFactory();
        $schematicFieldModel = $fieldFactory->build($fieldType);

        $this->assertInstanceOf($expectedClassName, $schematicFieldModel);
    }

    /**
     * @return array
     */
    public function provideFieldTypes()
    {
        return [
            // Base fields
            'Assets' => ['Assets', AssetsField::class],
            'Categories' => ['Category', Field::class],
            'Checkboxes' => ['Checkboxes', Field::class],
            'Color' => ['Color', Field::class],
            'Date' => ['Date', Field::class],
            'Dropdown' => ['Dropdown', Field::class],
            'Entries' => ['Entries', Field::class],
            'Lightswitch' => ['Lightswitch', Field::class],
            'Matrix' => ['Matrix', MatrixField::class],
            'MultiSelect' => ['Multiselect', Field::class],
            'Number' => ['Number', Field::class],
            'PlainText' => ['PlainText', Field::class],
            'PositionSelect' => ['PositionSelect', PositionSelectField::class],
            'RadioButtons' => ['RadioButtons', Field::class],
            'RichText' => ['RichText', Field::class],
            'Table' => ['Table', Field::class],
            'Tags' => ['Tags', Field::class],
            'Users' => ['Users', Field::class],
            // Plugin fields
            'SuperTable' => ['SuperTable', SuperTableField::class],
        ];
    }

    /**
     * @return array
     */
    public function provideHookedFieldTypes()
    {
        return [
            'color mapped to matrix field model' => [
                'fieldType' => 'Color',
                'expectedClassName' => MatrixField::class,
                'hookCallResults' => [
                    'plugin1' => [
                        'Color' => MatrixField::class,
                    ],
                ],
            ],
            'color mapped to wrong class type' => [
                'fieldType' => 'Color',
                'expectedClassName' => Field::class,
                'hookCallResults' => [
                    'plugin1' => [
                        'Color' => ColorFieldType::class,
                    ],
                ],
            ],
            'something mapped to matrix field model' => [
                'fieldType' => 'PlainText',
                'expectedClassName' => Field::class,
                'hookCallResults' => [
                    'plugin1' => [
                        'Color' => MatrixField::class,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $schematicFieldModels
     */
    private function setMockPluginsService(array $schematicFieldModels)
    {
        $mockPluginsService = $this->getMockBuilder(PluginsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPluginsService->expects($this->exactly(1))
            ->method('call')
            ->with('registerSchematicFieldModels')
            ->willReturn($schematicFieldModels);

        $this->setComponent(Craft::app(), 'plugins', $mockPluginsService);
    }
}
