<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseTest;
use Craft\AssetSourcesService;
use Craft\AssetSourceModel;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class AssetSourcesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Services\AssetSources
 * @covers ::__construct
 * @covers ::<!public>
 */
class AssetSourcesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidAssetSources
     *
     * @param AssetSourceModel[] $assetSources
     * @param array              $expectedResult
     */
    public function testSuccessfulExport(array $assetSources, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicAssetSourcesService = new AssetSources();

        $actualResult = $schematicAssetSourcesService->export($assetSources);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidAssetSourceDefinitions
     *
     * @param array $assetSourceDefinitions
     */
    public function testSuccessfulImport(array $assetSourceDefinitions)
    {
        $this->setMockAssetSourcesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicAssetSourcesService = new AssetSources();

        $import = $schematicAssetSourcesService->import($assetSourceDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidAssetSourceDefinitions
     *
     * @param array $assetSourceDefinitions
     */
    public function testImportWithForceOption(array $assetSourceDefinitions)
    {
        $this->setMockAssetSourcesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicAssetSourcesService = new AssetSources();

        $import = $schematicAssetSourcesService->import($assetSourceDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidAssetSources()
    {
        return [
            'emptyArray' => [
                'AssetSources' => [],
                'expectedResult' => [],
            ],
            'single asset source' => [
                'AssetSources' => [
                    'assetSource1' => $this->getMockAssetSource(1),
                ],
                'expectedResult' => [
                    'assetSourceHandle1' => [
                        'type' => null,
                        'name' => 'assetSourceName1',
                        'sortOrder' => null,
                        'settings' => null,
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
            'multiple asset sources' => [
                'AssetSources' => [
                    'assetSource1' => $this->getMockAssetSource(1),
                    'assetSource2' => $this->getMockAssetSource(2),
                ],
                'expectedResult' => [
                    'assetSourceHandle1' => [
                        'type' => null,
                        'name' => 'assetSourceName1',
                        'sortOrder' => null,
                        'settings' => null,
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                    'assetSourceHandle2' => [
                        'type' => null,
                        'name' => 'assetSourceName2',
                        'sortOrder' => null,
                        'settings' => null,
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidAssetSourceDefinitions()
    {
        return [
            'emptyArray' => [
                'assetSourceDefinitions' => [],
            ],
            'single group' => [
                'assetSourceDefinitions' => [
                    'assetSourceHandle1' => [
                        'type' => 'Local',
                        'name' => 'assetSourceName1',
                        'sortOrder' => 1,
                        'settings' => array(),
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $assetSourceId
     *
     * @return Mock|AssetSourceModel
     */
    private function getMockAssetSource($assetSourceId)
    {
        $mockAssetSource = $this->getMockBuilder(AssetSourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetSource->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $assetSourceId],
                ['fieldLayoutId', $assetSourceId],
                ['handle', 'assetSourceHandle'.$assetSourceId],
                ['name', 'assetSourceName'.$assetSourceId],
            ]);

        $mockAssetSource->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockAssetSource;
    }

    /**
     * @return Mock|CraftFieldsService
     */
    private function setMockFieldsService()
    {
        $mockFieldsService = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFieldsService->expects($this->any())
            ->method('getLayoutById')
            ->with($this->isType('integer'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);

        return $mockFieldsService;
    }

    /**
     * @return Mock|fields
     */
    private function setMockSchematicFields()
    {
        $mockSchematicFields = $this->getMockBuilder(Fields::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayoutDefinition')
            ->with($this->isInstanceOf(FieldLayoutModel::class))
            ->willReturn(['fields' => []]);

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayout')
            ->with($this->isType('array'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFields);

        return $mockSchematicFields;
    }

    /**
     * @return Mock|AssetSourcesService
     */
    private function setMockAssetSourcesService()
    {
        $mockAssetSourcesService = $this->getMockBuilder(AssetSourcesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllSources', 'saveSource', 'deleteSourceById'])
            ->getMock();

        $mockAssetSourcesService->expects($this->any())
            ->method('getAllSources')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'assetSources', $mockAssetSourcesService);

        return $mockAssetSourcesService;
    }

    /**
     * @return Mock|FieldLayoutModel
     */
    private function getMockFieldLayout()
    {
        $mockFieldLayout = $this->getMockBuilder(FieldLayoutModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mockFieldLayout;
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
