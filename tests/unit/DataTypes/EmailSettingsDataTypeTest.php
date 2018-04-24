<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Codeception\Test\Unit;

/**
 * Class EmailSettingsDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class EmailSettingsDataTypeTest extends Unit
{
    /**
     * @var EmailSettingsDataType
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
        $this->dataType = new EmailSettingsDataType();
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

        $this->assertSame('emailSettingsMapper', $result);
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
