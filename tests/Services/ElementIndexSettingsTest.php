<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\CategoryElementType;
use Craft\ElementIndexesService;
use Craft\ElementsService;
use Craft\EntryElementType;
use Craft\FieldModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ElementIndexSettingsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Services\ElementIndexSettings
 * @covers ::__construct
 * @covers ::<!public>
 */
class ElementIndexSettingsTest extends BaseTest
{
    /**
     * @var ElementIndexSettings
     */
    private $schematicElementIndexSettingsService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->schematicElementIndexSettingsService = new ElementIndexSettings();
        $this->setMockSources();
        $this->setMockFieldsService();
    }

    /**
     * @return ElementsService|Mock
     *
     * @param array $getAllElementTypesResponse
     *
     * @return Mock
     */
    protected function getMockElementsService($getAllElementTypesResponse = [])
    {
        $mock = $this->getMockBuilder(ElementsService::class)->getMock();
        $mock->expects($this->any())->method('getAllElementTypes')->willReturn($getAllElementTypesResponse);

        return $mock;
    }

    /**
     * @return ElementIndexesService|Mock
     * @return Mock
     */
    protected function getMockElementIndexesService()
    {
        $getSettingsResponse = $this->getElementIndexSettingsSavedData();
        $mock = $this->getMockBuilder(ElementIndexesService::class)->getMock();
        $mock->expects($this->any())->method('getSettings')->will($this->returnValueMap([
          ['Entry', $getSettingsResponse['Entry']],
          ['Category', $getSettingsResponse['Category']],
        ]));
        $mock->expects($this->any())->method('saveSettings')->willReturn(false);

        return $mock;
    }

    /**
     * Test default import functionality.
     *
     * @covers ::import
     */
    public function testImport()
    {
        $data = $this->getElementIndexSettingsExportedData();
        $mockElementIndexesService = $this->getMockElementIndexesService();
        $this->setComponent(Craft::app(), 'elementIndexes', $mockElementIndexesService);

        $import = $this->schematicElementIndexSettingsService->import($data);

        $this->assertTrue($import instanceof Result);
        $this->assertTrue($import->hasErrors());
    }

    /**
     * Test export functionality.
     *
     * @covers ::export
     */
    public function testExport()
    {
        $data = $this->getElementsData();
        $mockElementsService = $this->getMockElementsService($data);
        $this->setComponent(Craft::app(), 'elements', $mockElementsService);

        $data = $this->getElementIndexSettingsExportedData();
        $mockElementIndexesService = $this->getMockElementIndexesService();
        $this->setComponent(Craft::app(), 'elementIndexes', $mockElementIndexesService);

        $export = $this->schematicElementIndexSettingsService->export();
        $this->assertEquals($data, $export);
    }

    /**
     * Returns elements data.
     *
     * @return array
     */
    public function getElementsData()
    {
        return [
            new CategoryElementType(),
            new EntryElementType(),
        ];
    }

    /**
     * Returns element index settings saved data.
     *
     * @return array
     */
    private function getElementIndexSettingsSavedData()
    {
        return [
            'Category' => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'source:1'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '1' => 'section',
                            '2' => 'postDate',
                            '3' => 'expiryDate',
                            '4' => 'author',
                            '5' => 'link',
                            '6' => 'field:1',
                        ],
                    ],
                ],
            ],
            'Entry' => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'source:1'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '1' => 'section',
                            '2' => 'postDate',
                            '3' => 'expiryDate',
                            '4' => 'author',
                            '5' => 'link',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns element index settings exported data.
     *
     * @return array
     */
    private function getElementIndexSettingsExportedData()
    {
        return [
            'Category' => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'source:handle'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '1' => 'section',
                            '2' => 'postDate',
                            '3' => 'expiryDate',
                            '4' => 'author',
                            '5' => 'link',
                            '6' => 'field:handle',
                        ],
                    ],
                ],
            ],
            'Entry' => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'source:handle'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '1' => 'section',
                            '2' => 'postDate',
                            '3' => 'expiryDate',
                            '4' => 'author',
                            '5' => 'link',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Mock|Sources
     */
    private function setMockSources()
    {
        $mockSources = $this->getMockBuilder(Sources::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSources->expects($this->any())
            ->method('getSource')
            ->will($this->returnCallback(array($this, 'getMockSourceCallback')));

        $this->setComponent(Craft::app(), 'schematic_sources', $mockSources);

        return $mockSources;
    }

    /**
     * @param string $fieldType
     * @param string $source
     * @param string $fromIndex
     * @param string $toIndex
     *
     * @return string
     */
    public function getMockSourceCallback($fieldType, $source, $fromIndex, $toIndex)
    {
        switch ($source) {
            case 'source:handle':
                return 'source:1';
            case 'source:1':
                return 'source:handle';
            case 'field:handle':
                return 'field:1';
            case 'field:1':
                return 'field:handle';
            default:
                return $source;
        }
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
            ->method('getFieldById')
            ->with('1')
            ->willReturn(new FieldModel(['handle' => 'handle']));

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);

        return $mockFieldsService;
    }
}
