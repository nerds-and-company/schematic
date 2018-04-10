<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\AssetTransform;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Class AssetTransformsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ModelProcessorTest extends Unit
{
    /**
     * @var AssetTransforms
     */
    private $service;

    /**
     * Set the service.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        Schematic::$force = false;

        $this->service = new ModelProcessor();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideValidAssetTransforms
     *
     * @param AssetTransform[] $assetTransforms
     * @param array            $expectedResult
     */
    public function testSuccessfulExport(array $assetTransforms, array $expectedResult = [])
    {
        $actualResult = $this->service->export($assetTransforms);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideValidAssetTransformDefinitions
     *
     * @param array            $assetTransformDefinitions
     * @param AssetTransform[] $existingTransforms
     * @param int              $saveCount
     */
    public function testSuccessfulImport(array $assetTransformDefinitions, array $existingTransforms, int $saveCount)
    {
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->service->import($assetTransformDefinitions, $existingTransforms);
    }

    /**
     * @dataProvider provideValidAssetTransformDefinitions
     *
     * @param array            $assetTransformDefinitions
     * @param AssetTransform[] $existingTransforms
     * @param int              $saveCount
     * @param int              $deleteCount
     */
    public function testImportWithForceOption(array $assetTransformDefinitions, array $existingTransforms, int $saveCount, int $deleteCount)
    {
        Schematic::$force = true;
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->service->import($assetTransformDefinitions, $existingTransforms);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidAssetTransforms()
    {
        return [
            'emptyArray' => [
                'assetTransforms' => [],
                'expectedResult' => [],
            ],
            'single asset transform' => [
                'assetTransforms' => [
                    $this->getAssetTransform(1),
                ],
                'expectedResult' => [
                    'assetTransformHandle1' => $this->getAssetTransformDefinition(1),
                ],
            ],
            'multiple asset transforms' => [
                'assetTransforms' => [
                    $this->getAssetTransform(1),
                    $this->getAssetTransform(2),
                ],
                'expectedResult' => [
                    'assetTransformHandle1' => $this->getAssetTransformDefinition(1),
                    'assetTransformHandle2' => $this->getAssetTransformDefinition(2),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidAssetTransformDefinitions()
    {
        return [
            'emptyArray' => [
                'assetTransformDefinitions' => [],
                'existingTransforms' => [
                    $this->getAssetTransform(1),
                ],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single new group' => [
                'assetTransformDefinitions' => [
                    'assetTransformHandle1' => $this->getAssetTransformDefinition(1),
                    'assetTransformHandle2' => $this->getAssetTransformDefinition(2),
                ],
                'existingTransforms' => [
                    $this->getAssetTransform(1),
                ],
                'saveCount' => 1,
                'deleteCount' => 0,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param int $assetTransformId
     *
     * @return AssetTransform
     */
    private function getAssetTransform(int $assetTransformId)
    {
        return new AssetTransform([
            'id' => $assetTransformId,
            'handle' => 'assetTransformHandle'.$assetTransformId,
            'name' => 'assetTransformName'.$assetTransformId,
        ]);
    }

    /**
     * @param int $assetTransformId
     *
     * @return array
     */
    private function getAssetTransformDefinition(int $assetTransformId)
    {
        return [
          'class' => 'craft\models\AssetTransform',
          'attributes' => [
              'name' => 'assetTransformName'.$assetTransformId,
              'handle' => 'assetTransformHandle'.$assetTransformId,
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

    /**
     * Expect a number of transform saves.
     *
     * @param int $saveCount
     */
    private function expectSaves(int $saveCount)
    {
        Craft::$app->assetTransforms
                   ->expects($this->exactly($saveCount))
                   ->method('saveTransform')
                   ->willReturn(true);
    }

    /**
     * Expect a number of transform deletes.
     *
     * @param int $deleteCount
     */
    private function expectDeletes(int $deleteCount)
    {
        Craft::$app->assetTransforms
                    ->expects($this->exactly($deleteCount))
                    ->method('deleteTransform');
    }
}
