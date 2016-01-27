<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseTest;
use Craft\ElementsService;
use Craft\ElementIndexesService;
use Craft\CategoryElementType;
use Craft\EntryElementType;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ElementIndexSettingsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
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
     *
     * @param array $getSettingsResponse
     *
     * @return Mock
     */
    protected function getMockElementIndexesService($getSettingsResponse = [])
    {
        $mock = $this->getMockBuilder(ElementIndexesService::class)->getMock();
        $mock->expects($this->any())->method('getSettings')->willReturn($getSettingsResponse['Entry']);
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
        $data = $this->getElementIndexSettingsData();
        $mockElementIndexesService = $this->getMockElementIndexesService($data);
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

        $data = $this->getElementIndexSettingsData();
        $mockElementIndexesService = $this->getMockElementIndexesService($data);
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
     * Returns element index settings data.
     *
     * @return array
     */
    public function getElementIndexSettingsData()
    {
        return [
            'Category' => [
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
            'Entry' => [
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
}
