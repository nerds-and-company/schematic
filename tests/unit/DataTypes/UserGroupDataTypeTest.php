<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\UserGroup;
use Codeception\Test\Unit;

/**
 * Class UserGroupDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserGroupDataTypeTest extends Unit
{
    /**
     * @var UserGroupDataType
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
        $this->dataType = new UserGroupDataType();
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
        $records = [$this->getMockUserGroup()];

        Craft::$app->userGroups->expects($this->exactly(1))
                               ->method('getAllGroups')
                               ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|UserGroup
     */
    private function getMockUserGroup()
    {
        return $this->getMockBuilder(UserGroup::class)->getMock();
    }
}
