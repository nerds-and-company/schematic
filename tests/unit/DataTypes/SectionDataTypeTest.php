<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\Section;
use Codeception\Test\Unit;

/**
 * Class SectionDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SectionDataTypeTest extends Unit
{
    /**
     * @var SectionDataType
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
        $this->dataType = new SectionDataType();
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
        $records = [$this->getMockSection()];

        Craft::$app->sections->expects($this->exactly(1))
                             ->method('getAllSections')
                             ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|Section
     */
    private function getMockSection()
    {
        return $this->getMockBuilder(Section::class)->getMock();
    }
}
