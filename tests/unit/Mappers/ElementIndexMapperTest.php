<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\base\Field;
use Codeception\Test\Unit;

/**
 * Class ElementIndexSettingsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ElementIndexMapperTest extends Unit
{
    /**
     * @var ElementIndexMapper
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
        $this->setMockSectionsService();
        $this->setMockFieldsService();
        $this->setMockElementIndexesService();

        $this->mapper = new ElementIndexMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Test default import functionality.
     */
    public function testImport()
    {
        $data = $this->getElementIndexSettingsExportedData();

        $result = $this->mapper->import($data, $this->getElementsData());

        $this->assertSame([], $result);
    }

    /**
     * Test export functionality.
     */
    public function testExport()
    {
        $data = $this->getElementsData();
        $expected = $this->getElementIndexSettingsExportedData();

        $result = $this->mapper->export($data);
        $this->assertEquals($expected, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param array $getAllElementTypesResponse
     */
    protected function setMockElementsService($getAllElementTypesResponse = [])
    {
        Craft::$app->elementTypes->expects($this->any())
                                 ->method('getAllElementTypes')
                                 ->willReturn($getAllElementTypesResponse);
    }

    /**
     * St mock element index service.
     */
    protected function setMockElementIndexesService()
    {
        $getSettingsResponse = $this->getElementIndexSettingsSavedData();
        Craft::$app->elementIndexes
                   ->expects($this->any())
                   ->method('getSettings')
                   ->willReturnMap([
                      [Entry::class, $getSettingsResponse[Entry::class]],
                      [Category::class, $getSettingsResponse[Category::class]],
                   ]);

        Craft::$app->elementIndexes->expects($this->any())
                                   ->method('saveSettings')
                                   ->willReturn(false);
    }

    /**
     * Returns elements data.
     *
     * @return array
     */
    public function getElementsData()
    {
        return [
            Category::class,
            Entry::class,
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
            Category::class => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'section:1'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '0' => 'section',
                            '1' => 'postDate',
                            '2' => 'expiryDate',
                            '3' => 'author',
                            '4' => 'link',
                            '5' => 'field:1',
                        ],
                    ],
                ],
            ],
            Entry::class => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'section:1'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '0' => 'section',
                            '1' => 'postDate',
                            '2' => 'expiryDate',
                            '3' => 'author',
                            '4' => 'link',
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
            Category::class => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'section:handle'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '0' => 'section',
                            '1' => 'postDate',
                            '2' => 'expiryDate',
                            '3' => 'author',
                            '4' => 'link',
                            '5' => 'field:handle',
                        ],
                    ],
                ],
            ],
            Entry::class => [
                'sourceOrder' => [
                    ['heading', 'Channels'],
                    ['key', 'section:handle'],
                ],
                'sources' => [
                    '*' => [
                        'tableAttributes' => [
                            '0' => 'section',
                            '1' => 'postDate',
                            '2' => 'expiryDate',
                            '3' => 'author',
                            '4' => 'link',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Mock|Sources
     */
    private function setMockSectionsService()
    {
        $mockSection = $this->getMockBuilder(Section::class)->getMock();
        $mockSection->handle = 'handle';

        Craft::$app->sections->expects($this->any())
                             ->method('getSectionById')
                             ->with('1')
                             ->willReturn($mockSection);
    }

    /**
     * Set mock fields service.
     */
    private function setMockFieldsService()
    {
        $mockField = $this->getMockBuilder(Field::class)->getMock();
        $mockField->handle = 'handle';

        Craft::$app->fields->expects($this->any())
                            ->method('getFieldById')
                            ->with('1')
                            ->willReturn($mockField);
    }
}
