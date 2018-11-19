<?php

namespace NerdsAndCompany\Schematic\Converters\Base;

use Craft;
use craft\base\Field as FieldModel;
use craft\base\Volume as VolumeModel;
use craft\elements\Asset;
use craft\models\FieldLayout;
use craft\volumes\Local;
use Codeception\Test\Unit;

/**
 * Class VolumeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class VolumeTest extends Unit
{
    /**
     * @var Volume
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
        $this->converter = new Volume();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideVolumes
     *
     * @param VolumeModel $volume
     * @param array       $definition
     */
    public function testGetRecordDefinition(VolumeModel $volume, array $definition)
    {
        $result = $this->converter->getRecordDefinition($volume);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideVolumes
     *
     * @param VolumeModel $volume
     * @param array       $definition
     */
    public function testSaveRecord(VolumeModel $volume, array $definition)
    {
        Craft::$app->volumes->expects($this->exactly(1))
                            ->method('saveVolume')
                            ->with($volume)
                            ->willReturn(true);

        $result = $this->converter->saveRecord($volume, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideVolumes
     *
     * @param VolumeModel $volume
     */
    public function testDeleteRecord(VolumeModel $volume)
    {
        Craft::$app->volumes->expects($this->exactly(1))
                            ->method('deleteVolume')
                            ->with($volume);

        $this->converter->deleteRecord($volume);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideVolumes()
    {
        $mockVolume = $this->getMockVolume(1);

        return [
            'local volume' => [
                'volume' => $mockVolume,
                'definition' => $this->getMockVolumeDefinition($mockVolume),
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param int $volumeId
     *
     * @return Mock|VolumeModel
     */
    private function getMockVolume(int $volumeId)
    {
        $mockVolume = $this->getMockBuilder(Local::class)
                           ->setMethods(['getFieldLayout'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockVolume->id = $volumeId;
        $mockVolume->fieldLayoutId = $volumeId;
        $mockVolume->handle = 'volumeHandle'.$volumeId;
        $mockVolume->name = 'volumeName'.$volumeId;

        $mockField = $this->getMockbuilder(FieldModel::class)->getMock();
        $mockField->id = $volumeId;
        $mockField->handle = 'field'.$volumeId;
        $mockField->required = true;

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();
        $mockFieldLayout->type = Asset::class;

        $mockFieldLayout->expects($this->any())
                        ->method('getFields')
                        ->willReturn([$mockField]);

        $mockVolume->expects($this->any())
                   ->method('getFieldLayout')
                   ->willReturn($mockFieldLayout);

        return $mockVolume;
    }

    /**
     * @param VolumeModel $mockVolume
     *
     * @return array
     */
    private function getMockVolumeDefinition(VolumeModel $mockVolume)
    {
        $fieldDefs = [];
        foreach ($mockVolume->getFieldLayout()->getFields() as $field) {
            $fieldDefs[$field->handle] = $field->required;
        }

        return [
            'class' => get_class($mockVolume),
            'attributes' => [
                'path' => null,
                'name' => 'volumeName'.$mockVolume->id,
                'handle' => 'volumeHandle'.$mockVolume->id,
                'hasUrls' => null,
                'url' => null,
                'sortOrder' => null,
            ],
            'fieldLayout' => [
                'type' => Asset::class,
                'fields' => $fieldDefs,
            ],
        ];
    }
}
