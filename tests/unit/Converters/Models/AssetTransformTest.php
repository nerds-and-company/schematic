<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\AssetTransform as AssetTransformModel;
use Codeception\Test\Unit;

/**
 * Class AssetTransformTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetTransformTest extends Unit
{
    /**
     * @var AssetTransform
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->converter = new AssetTransform();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideAssetTransforms
     *
     * @param AssetTransformModel $transform
     * @param array               $definition
     */
    public function testGetRecordDefinition(AssetTransformModel $transform, array $definition)
    {
        $result = $this->converter->getRecordDefinition($transform);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideAssetTransforms
     *
     * @param AssetTransformModel $transform
     * @param array               $definition
     */
    public function testSaveRecord(AssetTransformModel $transform, array $definition)
    {
        Craft::$app->assetTransforms->expects($this->exactly(1))
                                    ->method('saveTransform')
                                    ->with($transform)
                                    ->willReturn(true);

        $result = $this->converter->saveRecord($transform, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideAssetTransforms
     *
     * @param AssetTransformModel $transform
     */
    public function testDeleteRecord(AssetTransformModel $transform)
    {
        Craft::$app->assetTransforms->expects($this->exactly(1))
                                    ->method('deleteTransform')
                                    ->with($transform->id);

        $this->converter->deleteRecord($transform);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideAssetTransforms()
    {
        $mockAssetTransform = $this->getMockAssetTransform(1);

        return [
            'valid transform existing group' => [
                'transform' => $mockAssetTransform,
                'definition' => $this->getMockAssetTransformDefinition($mockAssetTransform),
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param int $assetTransformId
     *
     * @return AssetTransformModel
     */
    private function getMockAssetTransform(int $assetTransformId)
    {
        return new AssetTransformModel([
            'id' => $assetTransformId,
            'handle' => 'assetTransformHandle'.$assetTransformId,
            'name' => 'assetTransformName'.$assetTransformId,
        ]);
    }

    /**
     * @param AssetTransformModel $assetTransform
     *
     * @return array
     */
    private function getMockAssetTransformDefinition(AssetTransformModel $assetTransform)
    {
        return [
          'class' => get_class($assetTransform),
          'attributes' => [
              'name' => 'assetTransformName'.$assetTransform->id,
              'handle' => 'assetTransformHandle'.$assetTransform->id,
              'width' => null,
              'height' => null,
              'format' => null,
              'mode' => 'crop',
              'position' => 'center-center',
              'interlace' => 'none',
              'quality' => null,
          ],
        ];
    }
}
