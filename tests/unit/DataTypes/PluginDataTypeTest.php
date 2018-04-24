<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use Codeception\Test\Unit;

/**
 * Class PluginDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PluginDataTypeTest extends Unit
{
    /**
     * @var PluginDataType
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
        $this->dataType = new PluginDataType();
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

        $this->assertSame('pluginMapper', $result);
    }

    /**
     * Get records test.
     */
    public function testGetRecords()
    {
        $records = [$this->getMockPluginInfo()];

        Craft::$app->plugins->expects($this->exactly(1))
                            ->method('getAllPluginInfo')
                            ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    private function getMockPluginInfo()
    {
        return ['pluginInfo'];
    }
}
