<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\fields\Assets as AssetsField;
use craft\base\Volume;
use craft\models\VolumeFolder;
use craft\records\VolumeFolder as VolumeFolderRecord;
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
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
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
     * @param Mock|Volume|null $mockFolder
     */
    public function testGetRecordDefinition(AssetsField $assets, array $definition, $mockFolder)
    {
        Craft::$app->assets->expects($this->any())
                           ->method('getFolderById')
                           ->with($mockFolder->id)
                           ->willReturn($mockFolder);

        $result = $this->converter->getRecordDefinition($assets);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideAssets
     *
     * @param AssetsField      $assets
     * @param array            $definition
     * @param Mock|Volume|null $mockFolder
     */
    public function testSetRecordAttributes(AssetsField $assets, array $definition, $mockFolder)
    {
        $this->setMockFolderRecord($mockFolder);
        $mockVolume = $mockFolder->getVolume();
        Craft::$app->volumes->expects($this->any())
                            ->method('getVolumeByHandle')
                            ->with($mockVolume->handle)
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
        $mockFolder1 = $this->getMockFolder(1);

        return [
            'assets' => [
                'Assets' => $mockAssets1,
                'definition' => $this->getMockAssetsDefinition($mockAssets1),
                'volume' => $mockFolder1,
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
     * Get a mock folder.
     *
     * @param int $folderId
     *
     * @return Mock|VolumeFolder
     */
    private function getMockFolder(int $folderId)
    {
        $mockVolume = $this->getMockVolume($folderId);
        $mockFolder = $this->getMockBuilder(VolumeFolder::class)
                           ->disableOriginalConstructor()
                           ->getmock();

        $mockFolder->id = $folderId;
        $mockFolder->handle = 'folderHandle'.$folderId;

        $mockFolder->expects($this->any())
                   ->method('getVolume')
                   ->willReturn($mockVolume);

        return $mockFolder;
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

    /**
     * Set a mock folder record on converter based on mock folder
     *
     * @param VolumeFolder $mockFolder
     */
    private function setMockFolderRecord(VolumeFolder $mockFolder)
    {
        $mockFolderRecord = $this->getMockBuilder(VolumeFolderRecord::class)->getMock();
        $mockFolderRecord->expects($this->any())
                         ->method('__get')
                         ->willReturnMap([['id', $mockFolder->id]]);
        $this->converter->setMockFolder($mockFolderRecord);
    }
}
