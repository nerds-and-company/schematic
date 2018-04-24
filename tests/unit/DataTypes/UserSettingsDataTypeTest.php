<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Codeception\Test\Unit;

/**
 * Class UserSettingsDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserSettingsDataTypeTest extends Unit
{
    /**
     * @var UserSettingsDataType
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
        $this->dataType = new UserSettingsDataType();
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

        $this->assertSame('userSettingsMapper', $result);
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
