<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\fields\Assets as AssetsField;
use craft\base\Volume;
use Codeception\Test\Unit;

/**
 * Class AssetsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class AssetsTest extends Unit
{
    /**
     * @var Assets
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $this->converter = new Assets();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideAssets
     *
     * @param AssetsField      $assets
     * @param array            $definition
     * @param Mock|Volume|null $mockVolume
     */
    public function testGetRecordDefinition(AssetsField $assets, array $definition, $mockVolume)
    {
        Craft::$app->volumes->expects($this->any())
                            ->method('getVolumeById')
                            ->with($mockVolume->id)
                            ->willReturn($mockVolume);

        $result = $this->converter->getRecordDefinition($assets);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideAssets
     *
     * @param AssetsField      $assets
     * @param array            $definition
     * @param Mock|Volume|null $mockVolume
     */
    public function testSetRecordAttributes(AssetsField $assets, array $definition, $mockVolume)
    {
        Craft::$app->volumes->expects($this->any())
                            ->method('getVolumeByHandle')
                            ->willReturn($mockVolume);

        $newAssets = new AssetsField();

        $this->converter->setRecordAttributes($newAssets, $definition, []);

        $this->assertSame($assets->name, $newAssets->name);
        $this->assertSame($assets->handle, $newAssets->handle);
        $this->assertSame($assets->defaultUploadLocationSource, $newAssets->defaultUploadLocationSource);
        $this->assertSame($assets->singleUploadLocationSource, $newAssets->singleUploadLocationSource);
        $this->assertSame($assets->sources, $newAssets->sources);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideAssets()
    {
        $mockAssets1 = $this->getMockAssets(1, 1);
        $mockVolume1 = $this->getMockVolume(1);

        return [
            'assets' => [
                'Assets' => $mockAssets1,
                'definition' => $this->getMockAssetsDefinition($mockAssets1),
                'volume' => $mockVolume1,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param AssetsField $mockAssets
     *
     * @return array
     */
    private function getMockAssetsDefinition(AssetsField $mockAssets)
    {
        return [
            'class' => get_class($mockAssets),
            'attributes' => [
                'useSingleFolder' => null,
                'defaultUploadLocationSource' => 'folder:volumeHandle1',
                'defaultUploadLocationSubpath' => null,
                'singleUploadLocationSource' => 'folder:volumeHandle1',
                'singleUploadLocationSubpath' => null,
                'restrictFiles' => null,
                'allowedKinds' => null,
                'sources' => [
                    'folder:volumeHandle1',
                ],
                'source' => null,
                'viewMode' => null,
                'limit' => null,
                'selectionLabel' => null,
                'localizeRelations' => false,
                'allowMultipleSources' => true,
                'allowLimit' => true,
                'name' => 'assetsName'.$mockAssets->id,
                'handle' => 'assetsHandle'.$mockAssets->id,
                'instructions' => null,
                'translationMethod' => 'none',
                'translationKeyFormat' => null,
                'oldHandle' => null,
                'columnPrefix' => null,
                'required' => false,
                'sortOrder' => null,
            ],
            'group' => 'fieldGroup1',
        ];
    }

    /**
     * @param int $assetsId
     * @param int $groupId
     *
     * @return Mock|AssetsField
     */
    private function getMockAssets(int $assetsId, int $groupId)
    {
        $mockAssets = $this->getMockBuilder(AssetsField::class)
                           ->setMethods(['getGroup', 'getBlockTypes'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockAssets->id = $assetsId;
        $mockAssets->groupId = $assetsId;
        $mockAssets->handle = 'assetsHandle'.$assetsId;
        $mockAssets->name = 'assetsName'.$assetsId;
        $mockAssets->defaultUploadLocationSource = 'folder:'.$assetsId;
        $mockAssets->singleUploadLocationSource = 'folder:'.$assetsId;
        $mockAssets->sources = ['folder:'.$assetsId];

        $mockAssets->expects($this->any())
                 ->method('getGroup')
                 ->willReturn($this->getMockFieldGroup($groupId));

        return $mockAssets;
    }

    /**
     * Get a mock Field group.
     *
     * @param int $groupId
     *
     * @return Mock|FieldGroup
     */
    private function getMockFieldGroup(int $groupId)
    {
        $mockGroup = $this->getMockBuilder(FieldGroup::class)
                          ->disableOriginalConstructor()
                          ->getmock();

        $mockGroup->id = $groupId;
        $mockGroup->name = 'fieldGroup'.$groupId;

        return $mockGroup;
    }

    /**
     * Get a mock volume.
     *
     * @param int $volumeId
     *
     * @return Mock|Volume
     */
    private function getMockVolume(int $volumeId)
    {
        $mockVolume = $this->getMockBuilder(Volume::class)
                           ->disableOriginalConstructor()
                           ->getmock();

        $mockVolume->id = $volumeId;
        $mockVolume->handle = 'volumeHandle'.$volumeId;

        return $mockVolume;
    }
}
