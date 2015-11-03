<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_FieldFactoryModelTest
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\Schematic_FieldFactoryModel
 * @covers ::<!public>
 */
class Schematic_FieldFactoryModelTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../models/Schematic_FieldFactoryModel.php';
        require_once __DIR__.'/../../models/Schematic_FieldModel.php';
        require_once __DIR__.'/../../models/Schematic_MatrixFieldModel.php';
        require_once __DIR__.'/../../models/Schematic_PositionSelectFieldModel.php';
        require_once __DIR__.'/../../models/Schematic_SuperTableFieldModel.php';
    }

    /**
     * @covers ::build
     * @dataProvider provideFieldTypes
     *
     * @param string $fieldType
     * @param string $expectedClassName
     */
    public function testBuildWithDefaultFieldTypesReturnsCorrectClass($fieldType, $expectedClassName)
    {
        $fieldFactory = new Schematic_FieldFactoryModel();
        $schematicFieldModel = $fieldFactory->build($fieldType);

        $this->assertEquals($expectedClassName, get_class($schematicFieldModel));
    }

    /**
     * @covers ::build
     * @dataProvider provideHookedFieldTypes
     *
     * @param string $fieldType
     * @param string $expectedClassName
     * @param array $hookCallResults
     */
    public function testBuildWithFieldHook($fieldType, $expectedClassName, array $hookCallResults)
    {
        $this->setMockPluginsService($hookCallResults);

        $fieldFactory = new Schematic_FieldFactoryModel();
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
            'Assets' => array('Assets', 'Craft\Schematic_FieldModel'),
            'Categories' => array('Category', 'Craft\Schematic_FieldModel'),
            'Checkboxes' => array('Checkboxes', 'Craft\Schematic_FieldModel'),
            'Color' => array('Color', 'Craft\Schematic_FieldModel'),
            'Date' => array('Date', 'Craft\Schematic_FieldModel'),
            'Dropdown' => array('Dropdown', 'Craft\Schematic_FieldModel'),
            'Entries' => array('Entries', 'Craft\Schematic_FieldModel'),
            'Lightswitch' => array('Lightswitch', 'Craft\Schematic_FieldModel'),
            'Matrix' => array('Matrix', 'Craft\Schematic_MatrixFieldModel'),
            'MultiSelect' => array('Multiselect', 'Craft\Schematic_FieldModel'),
            'Number' => array('Number', 'Craft\Schematic_FieldModel'),
            'PlainText' => array('PlainText', 'Craft\Schematic_FieldModel'),
            'PositionSelect' => array('PositionSelect', 'Craft\Schematic_PositionSelectFieldModel'),
            'RadioButtons' => array('RadioButtons', 'Craft\Schematic_FieldModel'),
            'RichText' => array('RichText', 'Craft\Schematic_FieldModel'),
            'Table' => array('Table', 'Craft\Schematic_FieldModel'),
            'Tags' => array('Tags', 'Craft\Schematic_FieldModel'),
            'Users' => array('Users', 'Craft\Schematic_FieldModel'),
            // Plugin fields
            'SuperTable' => array('SuperTable', 'Craft\Schematic_SuperTableFieldModel'),
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
                'expectedClassName' => 'Craft\Schematic_MatrixFieldModel',
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => 'Craft\Schematic_MatrixFieldModel'
                    )
                )
            ),
            'color mapped to wrong class type' => array(
                'fieldType' => 'Color',
                'expectedClassName' => 'Craft\Schematic_FieldModel',
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => 'Craft\ColorFieldType'
                    )
                )
            ),
            'something mapped to matrix field model' => array(
                'fieldType' => 'PlainText',
                'expectedClassName' => 'Craft\Schematic_FieldModel',
                'hookCallResults' => array(
                    'plugin1' => array(
                        'Color' => 'Craft\Schematic_MatrixFieldModel'
                    )
                )
            )
        );
    }

    /**
     * @param array $schematicFieldModels
     */
    private function setMockPluginsService( array $schematicFieldModels )
    {
        $mockPluginsService = $this->getMockBuilder('Craft\PluginsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPluginsService->expects($this->exactly(1))
            ->method('call')
            ->with('registerSchematicFieldModels')
            ->willReturn($schematicFieldModels);

        $this->setComponent(craft(), 'plugins', $mockPluginsService);
    }
}
