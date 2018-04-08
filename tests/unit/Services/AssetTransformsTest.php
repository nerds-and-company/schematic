<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\AssetTransform;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;
use craft\services\AssetTransforms as CraftAssetTransforms;
use craft\console\Application;

/**
 * Class AssetTransformsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetTransformsTest extends Unit
{
    private $service;

    /**
     * Set the service.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $mockApp = $this->getMockBuilder(Application::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $mockAssetTransforms = $this->getMockBuilder(CraftAssetTransforms::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockApp->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['assetTransforms', $mockAssetTransforms],
            ]);

        Craft::$app = $mockApp;
        Schematic::$force = false;

        $this->service = new AssetTransforms();
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
        $this->expectList($existingTransforms);
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->service->import($assetTransformDefinitions);
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
        $this->expectList($existingTransforms);
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->service->import($assetTransformDefinitions);
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
                    $this->getMockAssetTransform(1),
                ],
                'expectedResult' => [
                    'assetTransformHandle1' => $this->getAssetTransformDefinition(1),
                ],
            ],
            'multiple asset transforms' => [
                'assetTransforms' => [
                    $this->getMockAssetTransform(1),
                    $this->getMockAssetTransform(2),
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
                    $this->getMockAssetTransform(1),
                ],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single group' => [
                'assetTransformDefinitions' => [
                    'assetTransformHandle1' => $this->getAssetTransformDefinition(1),
                    'assetTransformHandle2' => $this->getAssetTransformDefinition(2),
                ],
                'existingTransforms' => [
                    $this->getMockAssetTransform(1),
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
     * @return Mock|AssetTransform
     */
    private function getMockAssetTransform(int $assetTransformId)
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

    private function expectList(array $assetTransforms)
    {
        Craft::$app->assetTransforms
                   ->expects($this->exactly(1))
                   ->method('getAllTransforms')
                   ->willReturn($assetTransforms);
    }

    private function expectSaves(int $saveCount)
    {
        Craft::$app->assetTransforms
                   ->expects($this->exactly($saveCount))
                   ->method('saveTransform')
                   ->willReturn(true);
    }

    private function expectDeletes(int $deleteCount)
    {
        Craft::$app->assetTransforms
                    ->expects($this->exactly($deleteCount))
                    ->method('deleteTransform');
    }
}
