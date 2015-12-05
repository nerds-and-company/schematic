<?php

namespace NerdsAndCompany\SchematicTests\Models;

use Craft\BaseTest;
use Craft\ColorFieldType;
use Craft\PluginsService;

/**
 * Class FieldFactoryTest.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
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
        return array(
            // Base fields
            'Assets' => array('Assets', Field::class),
            'Categories' => array('Category', Field::class),
            'Checkboxes' => array('Checkboxes', Field::class),
            'Color' => array('Color', Field::class),
            'Date' => array('Date', Field::class),
            'Dropdown' => array('Dropdown', Field::class),
            'Entries' => array('Entries', Field::class),
            'Lightswitch' => array('Lightswitch', Field::class),
            'Matrix' => array('Matrix', MatrixField::class),
            'MultiSelect' => array('Multiselect', Field::class),
            'Number' => array('Number', Field::class),
            'PlainText' => array('PlainText', Field::class),
            'PositionSelect' => array('PositionSelect', PositionSelectField::class),
            'RadioButtons' => array('RadioButtons', Field::class),
            'RichText' => array('RichText', Field::class),
            'Table' => array('Table', Field::class),
            'Tags' => array('Tags', Field::class),
            'Users' => array('Users', Field::class),
            // Plugin fields
            'SuperTable' => array('SuperTable', SuperTableField::class),
        );
    }

    /**
     * @return array
     */
    public function provideHookedFieldTypes()
    {
        return array(
            'color mapped to matrix field model' => array(
                'fieldType' => 'Color',
                'expectedClassName' => MatrixField::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => MatrixField::class,
                    ),
                ),
            ),
            'color mapped to wrong class type' => array(
                'fieldType' => 'Color',
                'expectedClassName' => Field::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => ColorFieldType::class,
                    ),
                ),
            ),
            'something mapped to matrix field model' => array(
                'fieldType' => 'PlainText',
                'expectedClassName' => Field::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => MatrixField::class,
                    ),
                ),
            ),
        );
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

        $this->setComponent(craft(), 'plugins', $mockPluginsService);
    }
}
