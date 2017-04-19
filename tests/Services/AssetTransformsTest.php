<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\AssetTransformsService;
use Craft\AssetTransformModel;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class AssetTransformsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Services\AssetTransforms
 * @covers ::__construct
 * @covers ::<!public>
 */
class AssetTransformsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidAssetTransforms
     *
     * @param AssetTransformModel[] $assetTransforms
     * @param array                 $expectedResult
     */
    public function testSuccessfulExport(array $assetTransforms, array $expectedResult = [])
    {
        $schematicAssetTransformsService = new AssetTransforms();

        $actualResult = $schematicAssetTransformsService->export($assetTransforms);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidAssetTransformDefinitions
     *
     * @param array $assetTransformDefinitions
     */
    public function testSuccessfulImport(array $assetTransformDefinitions)
    {
        $this->setMockAssetTransformsService();
        $this->setMockDbConnection();

        $schematicAssetTransformsService = new AssetTransforms();

        $import = $schematicAssetTransformsService->import($assetTransformDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidAssetTransformDefinitions
     *
     * @param array $assetTransformDefinitions
     */
    public function testImportWithForceOption(array $assetTransformDefinitions)
    {
        $this->setMockAssetTransformsService();
        $this->setMockDbConnection();

        $schematicAssetTransformsService = new AssetTransforms();

        $import = $schematicAssetTransformsService->import($assetTransformDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
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
                'AssetTransforms' => [],
                'expectedResult' => [],
            ],
            'single asset source' => [
                'AssetTransforms' => [
                    'assetTransform1' => $this->getMockAssetTransform(1),
                ],
                'expectedResult' => [
                    'assetTransformHandle1' => [
                        'name' => 'assetTransformName1',
                        'width' => null,
                        'height' => null,
                        'format' => null,
                        'mode' => null,
                        'position' => null,
                        'quality' => null,
                    ],
                ],
            ],
            'multiple asset sources' => [
                'AssetTransforms' => [
                    'assetTransform1' => $this->getMockAssetTransform(1),
                    'assetTransform2' => $this->getMockAssetTransform(2),
                ],
                'expectedResult' => [
                    'assetTransformHandle1' => [
                        'name' => 'assetTransformName1',
                        'width' => null,
                        'height' => null,
                        'format' => null,
                        'mode' => null,
                        'position' => null,
                        'quality' => null,
                    ],
                    'assetTransformHandle2' => [
                        'name' => 'assetTransformName2',
                        'width' => null,
                        'height' => null,
                        'format' => null,
                        'mode' => null,
                        'position' => null,
                        'quality' => null,
                    ],
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
            ],
            'single group' => [
                'assetTransformDefinitions' => [
                    'assetTransformHandle1' => [
                        'name' => 'assetTransformName1',
                        'width' => 100,
                        'height' => 100,
                        'format' => 'jpg',
                        'mode' => 'crop',
                        'position' => 'center-center',
                        'quality' => 75,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $assetTransformId
     *
     * @return Mock|AssetTransformModel
     */
    private function getMockAssetTransform($assetTransformId)
    {
        $mockAssetTransform = $this->getMockBuilder(AssetTransformModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetTransform->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $assetTransformId],
                ['handle', 'assetTransformHandle'.$assetTransformId],
                ['name', 'assetTransformName'.$assetTransformId],
            ]);

        $mockAssetTransform->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockAssetTransform;
    }

    /**
     * @return Mock|AssetTransformsService
     */
    private function setMockAssetTransformsService()
    {
        $mockAssetTransformsService = $this->getMockBuilder(AssetTransformsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTransforms', 'saveTransform', 'deleteTransform'])
            ->getMock();

        $mockAssetTransformsService->expects($this->any())
            ->method('getAllTransforms')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'assetTransforms', $mockAssetTransformsService);

        return $mockAssetTransformsService;
    }

    /**
     * @return Mock|DbConnection
     */
    private function setMockDbConnection()
    {
        $mockDbConnection = $this->getMockBuilder(DbConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommand'])
            ->getMock();
        $mockDbConnection->autoConnect = false; // Do not auto connect

        $mockDbCommand = $this->getMockDbCommand();
        $mockDbConnection->expects($this->any())->method('createCommand')->willReturn($mockDbCommand);

        Craft::app()->setComponent('db', $mockDbConnection);

        return $mockDbConnection;
    }

    /**
     * @return Mock|DbCommand
     */
    private function getMockDbCommand()
    {
        $mockDbCommand = $this->getMockBuilder(DbCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['insertOrUpdate'])
            ->getMock();

        return $mockDbCommand;
    }
}
