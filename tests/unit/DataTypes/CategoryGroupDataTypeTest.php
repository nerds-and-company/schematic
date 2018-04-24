<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\CategoryGroup;
use Codeception\Test\Unit;

/**
 * Class CategoryGroupDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CategoryGroupDataTypeTest extends Unit
{
    /**
     * @var CategoryGroupDataType
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
        $this->dataType = new CategoryGroupDataType();
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
        $records = [$this->getMockCategoryGroup()];

        Craft::$app->categories->expects($this->exactly(1))
                               ->method('getAllGroups')
                               ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|CategoryGroup
     */
    private function getMockCategoryGroup()
    {
        return $this->getMockBuilder(CategoryGroup::class)->getMock();
    }
}
