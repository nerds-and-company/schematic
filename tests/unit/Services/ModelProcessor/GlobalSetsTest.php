<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\GlobalSet;
use craft\models\FieldLayout;
use craft\models\Site;
use craft\services\Fields;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Class GlobalSetsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSetsTest extends Unit
{
    /**
     * @var GlobalSets
     */
    private $service;

    /**
     * Set the service.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        Craft::$app->sites->expects($this->any())
                  ->method('getSiteByHandle')
                  ->willReturn($this->getMockSite());

        $this->service = new ModelProcessor();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideValidGlobalSets
     *
     * @param GlobalSetModel[] $sets
     * @param array            $expectedResult
     */
    public function testSuccessfulExport(array $sets, array $expectedResult = [])
    {
        $actualResult = $this->service->export($sets);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideValidGlobalSetDefinitions
     *
     * @param array $setDefinitions
     */
    public function testSuccessfulImport(array $setDefinitions, array $existingSets, int $saveCount)
    {
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->service->import($setDefinitions, $existingSets);
    }

    /**
     * @dataProvider provideValidGlobalSetDefinitions
     *
     * @param array $setDefinitions
     */
    public function testImportWithForceOption(array $setDefinitions, array $existingSets, int $saveCount, int $deleteCount)
    {
        Schematic::$force = true;
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->service->import($setDefinitions, $existingSets);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidGlobalSets()
    {
        $mockGlobalSet1 = $this->getMockGlobalSet(1);
        $mockGlobalSet2 = $this->getMockGlobalSet(2);

        return [
            'emptyArray' => [
                'GlobalSets' => [],
                'expectedResult' => [],
            ],
            'single set' => [
                'GlobalSets' => [
                    'set1' => $mockGlobalSet1,
                ],
                'expectedResult' => [
                    'setHandle1' => $this->getMockGlobalSetDefinition($mockGlobalSet1),
                ],
            ],
            'multiple sets' => [
                'GlobalSets' => [
                    'set1' => $mockGlobalSet1,
                    'set2' => $mockGlobalSet2,
                ],
                'expectedResult' => [
                    'setHandle1' => $this->getMockGlobalSetDefinition($mockGlobalSet1),
                    'setHandle2' => $this->getMockGlobalSetDefinition($mockGlobalSet2),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidGlobalSetDefinitions()
    {
        $mockGlobalSet1 = $this->getMockGlobalSet(1);
        $mockGlobalSet2 = $this->getMockGlobalSet(2);

        return [
            'emptyArray' => [
                'setDefinitions' => [],
                'existingSets' => [
                    $mockGlobalSet1,
                ],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single set' => [
                'setDefinitions' => [
                    'setHandle1' => $this->getMockGlobalSetDefinition($mockGlobalSet1),
                    'setHandle2' => $this->getMockGlobalSetDefinition($mockGlobalSet2),
                ],
                'existingSets' => [
                    $mockGlobalSet1,
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
     * @param GlobalSet $mockGlobalSet
     *
     * @return array
     */
    private function getMockGlobalSetDefinition(GlobalSet $mockGlobalSet)
    {
        return [
            'class' => get_class($mockGlobalSet),
            'attributes' => [
                'name' => $mockGlobalSet->name,
                'handle' => $mockGlobalSet->handle,
                'enabled' => true,
                'archived' => false,
                'enabledForSite' => true,
                'title' => null,
                'slug' => null,
                'uri' => null,
                'hasDescendants' => null,
                'ref' => null,
                'status' => null,
                'totalDescendants' => null,
                'url' => null,
                'text' => null,
            ],
            'fieldLayout' => [
                'fields' => [],
            ],
            'site' => 'default',
        ];
    }

    /**
     * @param int $setId
     *
     * @return Mock|GlobalSet
     */
    private function getMockGlobalSet(int $setId)
    {
        $mockSet = $this->getMockBuilder(GlobalSet::class)
                                    ->setMethods(array_diff(
                                        get_class_methods(GlobalSet::class),
                                        ['setAttributes', 'safeAttributes', 'getAttributes', 'getFields']
                                    ))
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockSet->expects($this->any())
          ->method('attributes')
          ->willReturn([
              'id',
              'fieldLayoutId',
              'siteId',
              'structureId',
              'tempId',
              'uid',
              'contentId',
              'name',
              'handle',
              'enabled',
              'archived',
              'enabledForSite',
              'title',
              'slug',
              'uri',
              'hasDescendants',
              'ref',
              'status',
              'structureId',
              'totalDescendants',
              'url',
              'text',
          ]);

        $mockSet->id = $setId;
        $mockSet->fieldLayoutId = $setId;
        $mockSet->handle = 'setHandle'.$setId;
        $mockSet->name = 'setName'.$setId;

        $mockSet->expects($this->any())
                ->method('getSite')
                ->willReturn($this->getMockSite());

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();

        $mockFieldLayout->expects($this->any())
                        ->method('getFields')
                        ->willReturn([]);

        $mockSet->expects($this->any())
                  ->method('getFieldLayout')
                  ->willReturn($mockFieldLayout);

        return $mockSet;
    }

    /**
     * Get a mock site.
     *
     * @return Mock|Site
     */
    private function getMockSite()
    {
        $mockSite = new Site([
            'id' => 99,
            'handle' => 'default',
        ]);

        return $mockSite;
    }

    /**
     * Expect a number of set saves.
     *
     * @param int $saveCount
     */
    private function expectSaves(int $saveCount)
    {
        Craft::$app->globals
                   ->expects($this->exactly($saveCount))
                   ->method('saveSet')
                   ->willReturn(true);
    }

    /**
     * Expect a number of set deletes.
     *
     * @param int $deleteCount
     */
    private function expectDeletes(int $deleteCount)
    {
        Craft::$app->elements
                    ->expects($this->exactly($deleteCount))
                    ->method('deleteElementById');
    }
}
