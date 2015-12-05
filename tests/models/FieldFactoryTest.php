<?php

namespace NerdsAndCompany\SchematicTests\Models;

use Craft\Craft;
use Craft\BaseTest;
use Craft\ColorFieldType;
use Craft\PluginsService;
use NerdsAndCompany\Schematic\Models as Model;

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
        $fieldFactory = new Model\FieldFactory();
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

        $fieldFactory = new Model\FieldFactory();
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
            'Assets' => array('Assets', Model\Field::class),
            'Categories' => array('Category', Model\Field::class),
            'Checkboxes' => array('Checkboxes', Model\Field::class),
            'Color' => array('Color', Model\Field::class),
            'Date' => array('Date', Model\Field::class),
            'Dropdown' => array('Dropdown', Model\Field::class),
            'Entries' => array('Entries', Model\Field::class),
            'Lightswitch' => array('Lightswitch', Model\Field::class),
            'Matrix' => array('Matrix', Model\MatrixField::class),
            'MultiSelect' => array('Multiselect', Model\Field::class),
            'Number' => array('Number', Model\Field::class),
            'PlainText' => array('PlainText', Model\Field::class),
            'PositionSelect' => array('PositionSelect', Model\PositionSelectField::class),
            'RadioButtons' => array('RadioButtons', Model\Field::class),
            'RichText' => array('RichText', Model\Field::class),
            'Table' => array('Table', Model\Field::class),
            'Tags' => array('Tags', Model\Field::class),
            'Users' => array('Users', Model\Field::class),
            // Plugin fields
            'SuperTable' => array('SuperTable', Model\SuperTableField::class),
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
                'expectedClassName' => Model\MatrixField::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => Model\MatrixField::class,
                    ),
                ),
            ),
            'color mapped to wrong class type' => array(
                'fieldType' => 'Color',
                'expectedClassName' => Model\Field::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => ColorFieldType::class,
                    ),
                ),
            ),
            'something mapped to matrix field model' => array(
                'fieldType' => 'PlainText',
                'expectedClassName' => Model\Field::class,
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => Model\MatrixField::class,
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

        $this->setComponent(Craft::app(), 'plugins', $mockPluginsService);
    }
}
