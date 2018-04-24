<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\GlobalSet;
use Codeception\Test\Unit;

/**
 * Class GlobalSetDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSetDataTypeTest extends Unit
{
    /**
     * @var GlobalSetDataType
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
        $this->dataType = new GlobalSetDataType();
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

        $this->assertSame('modelMapper', $result);
    }

    /**
     * Get records test.
     */
    public function testGetRecords()
    {
        $records = [$this->getMockGlobalSet()];

        Craft::$app->globals->expects($this->exactly(1))
                            ->method('getAllSets')
                            ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|GlobalSet
     */
    private function getMockGlobalSet()
    {
        return $this->getMockBuilder(GlobalSet::class)->getMock();
    }
}
