<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\ElementType;
use Codeception\Test\Unit;

/**
 * Class ElementTypeDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ElementIndexDataTypeTest extends Unit
{
    /**
     * @var ElementIndexDataType
     */
    private $dataType;

    /**
     * Set the dataType.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->dataType = new ElementIndexDataType();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Get mapper handle test.
     */
    public function testGetMapperHandle()
    {
        $result = $this->dataType->getMapperHandle();

        $this->assertSame('elementIndexMapper', $result);
    }

    /**
     * Get records test.
     */
    public function testGetRecords()
    {
        $records = [$this->getMockElementType()];

        Craft::$app->elements->expects($this->exactly(1))
                             ->method('getAllElementTypes')
                             ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|ElementType
     */
    private function getMockElementType()
    {
        return $this->getMockBuilder(ElementType::class)->getMock();
    }
}
