<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use craft\models\AssetTransform;
use Codeception\Test\Unit;

/**
 * Class AssetTransformDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetTransformDataTypeTest extends Unit
{
    /**
     * @var AssetTransformDataType
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
        $this->dataType = new AssetTransformDataType();
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
        $records = [$this->getMockAssetTransform()];

        Craft::$app->assetTransforms->expects($this->exactly(1))
                                    ->method('getAllTransforms')
                                    ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|AssetTransform
     */
    private function getMockAssetTransform()
    {
        return $this->getMockBuilder(AssetTransform::class)->getMock();
    }
}
