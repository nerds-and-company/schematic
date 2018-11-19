<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use craft\base\Model;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Models\Base as Converter;

/**
 * Class ModelMapperTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ModelMapperTest extends Unit
{
    /**
     * @var ModelMapper
     */
    private $mapper;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->mapper = new ModelMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideExportModels
     *
     * @param Model[] $models
     * @param array   $expectedResult
     */
    public function testSuccessfulExport(array $models, array $expectedResult = [])
    {
        $converter = $this->getMockConverter();
        $this->expectConverter($converter, count($models));

        $actualResult = $this->mapper->export($models);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideImportModels
     *
     * @param Model[] $existingGroups
     * @param array   $modelDefinitions
     * @param int     $saveCount
     */
    public function testUnSuccessfulImport(array $existingGroups, array $modelDefinitions, int $saveCount)
    {
        $converter = $this->getMockConverter();
        $this->expectConverter($converter, count($existingGroups) + count($modelDefinitions));
        $this->expectSaves($converter, $saveCount, false);
        $this->expectDeletes($converter, 0);

        $this->mapper->import($modelDefinitions, $existingGroups);
    }

    /**
     * @dataProvider provideImportModels
     *
     * @param Model[] $existingGroups
     * @param array   $modelDefinitions
     * @param int     $saveCount
     */
    public function testSuccessfulImport(array $existingGroups, array $modelDefinitions, int $saveCount)
    {
        $converter = $this->getMockConverter();
        $this->expectConverter($converter, count($existingGroups) + count($modelDefinitions));
        $this->expectSaves($converter, $saveCount);
        $this->expectDeletes($converter, 0);

        $this->mapper->import($modelDefinitions, $existingGroups);
    }

    /**
     * @dataProvider provideImportModels
     *
     * @param Model[] $existingGroups
     * @param array   $modelDefinitions
     * @param int     $saveCount
     * @param int     $deleteCount
     */
    public function testSuccessfulImportWithForceOption(
        array $existingGroups,
        array $modelDefinitions,
        int $saveCount,
        int $deleteCount
    ) {
        Schematic::$force = true;
        $converter = $this->getMockConverter();
        $this->expectConverter($converter, count($existingGroups) + count($modelDefinitions) + $deleteCount);
        $this->expectSaves($converter, $saveCount);
        $this->expectDeletes($converter, $deleteCount);

        $this->mapper->import($modelDefinitions, $existingGroups);
    }

    /**
     * @dataProvider provideImportModels
     *
     * @param Model[] $existingGroups
     * @param array   $modelDefinitions
     * @param int     $saveCount
     * @param int     $deleteCount
     */
    public function testUnsuccessfulImportWithForceOption(
        array $existingGroups,
        array $modelDefinitions,
        int $saveCount,
        int $deleteCount
    ) {
        Schematic::$force = true;
        $converter = $this->getMockConverter();
        $this->expectConverter($converter, count($existingGroups) + count($modelDefinitions) + $deleteCount);
        $this->expectSaves($converter, $saveCount, false);
        $this->expectDeletes($converter, $deleteCount);

        $this->mapper->import($modelDefinitions, $existingGroups);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideExportModels()
    {
        $mockModel1 = $this->getMockModel(1);
        $mockModel2 = $this->getMockModel(2);

        return [
            'empty array' => [
                'models' => [],
                'modelDefinitions' => [],
            ],
            'single model' => [
                'models' => [
                    $mockModel1,
                ],
                'modelDefinitions' => [
                    'modelHandle1' => $this->getMockModelDefinition($mockModel1),
                ],
            ],
            'multiple models' => [
                'models' => [
                    $mockModel1,
                    $mockModel2,
                ],
                'modelDefinitions' => [
                    'modelHandle1' => $this->getMockModelDefinition($mockModel1),
                    'modelHandle2' => $this->getMockModelDefinition($mockModel2),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideImportModels()
    {
        $mockModel1 = $this->getMockModel(1);
        $mockModel2 = $this->getMockModel(2);

        return [
            'empty array' => [
                'models' => [],
                'modelDefinitions' => [],
                'saveCount' => 0,
                'deleteCount' => 0,
            ],
            'single old model' => [
                'models' => [
                    $mockModel1,
                ],
                'modelDefinitions' => [],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single new model' => [
                'models' => [
                    $mockModel1,
                ],
                'modelDefinitions' => [
                    'modelHandle1' => $this->getMockModelDefinition($mockModel1),
                    'modelHandle2' => $this->getMockModelDefinition($mockModel2),
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
     * Get model definition for mock model.
     *
     * @param Model $mockModel
     *
     * @return array
     */
    private function getMockModelDefinition(Model $mockModel): array
    {
        return [
            'class' => get_class($mockModel),
            'attributes' => [
                'name' => $mockModel->name,
                'handle' => $mockModel->handle,
                'max' => 100,
                'other' => 'stuff',
            ],
        ];
    }

    /**
     * Get a mock model.
     *
     * @param int $modelId
     *
     * @return Mock|Model
     */
    private function getMockModel(int $modelId): Model
    {
        $mockModel = $this->getMockBuilder(Model::class)->getMock();
        $mockModel->expects($this->any())
                  ->method('__get')
                  ->willReturnMap([
                        ['id', $modelId],
                        ['handle', 'modelHandle'.$modelId],
                        ['name', 'modelName'.$modelId],
                  ]);

        return $mockModel;
    }

    /**
     * Get a mock converter.
     *
     * @return Converter
     */
    private function getMockConverter(): Converter
    {
        $mockConverter = $this->getMockBuilder(Converter::class)->getMock();

        $mockConverter->expects($this->any())
                      ->method('getRecordIndex')
                      ->willReturnCallback(function ($model) {
                        return $model->handle;
                      });

        $mockConverter->expects($this->any())
                      ->method('getRecordDefinition')
                      ->willReturnCallback(function ($model) {
                          return $this->getMockModelDefinition($model);
                      });

        return $mockConverter;
    }

    /**
     * Mock a converter.
     *
     * @param Mock|Converter|null $converter
     */
    private function expectConverter($converter, int $count)
    {
        Craft::$app->controller->module->expects($this->exactly($count))
                                       ->method('getConverter')
                                       ->willReturn($converter);
    }

    /**
     * Expect a number of model saves.
     *
     * @param Converter $converter
     * @param int       $saveCount
     * @param bool      $return
     */
    private function expectSaves(Converter $converter, int $saveCount, bool $return = true)
    {
        $converter->expects($this->exactly($saveCount))
                  ->method('saveRecord')
                  ->willReturn($return);
    }

    /**
     * Expect a number of model deletes.
     *
     * @param Converter $converter
     * @param int       $deleteCount
     */
    private function expectDeletes(Converter $converter, int $deleteCount)
    {
        $converter->expects($this->exactly($deleteCount))
                  ->method('deleteRecord');
    }
}
