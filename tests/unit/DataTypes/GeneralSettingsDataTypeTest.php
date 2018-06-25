<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Codeception\Test\Unit;

/**
 * Class GeneralSettingsDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GeneralSettingsDataTypeTest extends Unit
{
    /**
     * @var GeneralSettingsDataType
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
        $this->dataType = new GeneralSettingsDataType();
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

        $this->assertSame('generalSettingsMapper', $result);
    }

    /**
     * Get records test.
     */
    public function testGetRecords()
    {
        $result = $this->dataType->getRecords();

        $this->assertSame([], $result);
    }
}
