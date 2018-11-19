<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Field as FieldModel;
use craft\elements\Entry;
use craft\models\EntryType as EntryTypeModel;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use Codeception\Test\Unit;

/**
 * Class EntryTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class EntryTypeTest extends Unit
{
    /**
     * @var EntryType
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
        $this->converter = new EntryType();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideEntryTypes
     *
     * @param EntryTypeModel $entryType
     * @param array          $definition
     */
    public function testGetRecordDefinition(EntryTypeModel $entryType, array $definition)
    {
        $result = $this->converter->getRecordDefinition($entryType);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideEntryTypes
     *
     * @param EntryTypeModel $entryType
     * @param array          $definition
     * @param array          $defaultAttributes
     */
    public function testSetRecordAttributes(EntryTypeModel $entryType, array $definition, array $defaultAttributes)
    {
        $newEntryType = $this->getMockBuilder(EntryTypeModel::class)
                             ->setMethods(['setFieldLayout'])
                             ->getMock();

        $newEntryType->expects($this->exactly(1))
                     ->method('setFieldLayout');

        Craft::$app->fields->expects($this->any())
                           ->method('getFieldByHandle')
                           ->willReturn($this->getMockField($entryType->id));

        $this->converter->setRecordAttributes($newEntryType, $definition, $defaultAttributes);

        $this->assertSame($defaultAttributes['sectionId'], $newEntryType->sectionId);
        $this->assertSame($entryType->name, $newEntryType->name);
        $this->assertSame($entryType->handle, $newEntryType->handle);
    }

    /**
     * @dataProvider provideEntryTypes
     *
     * @param EntryTypeModel $entryType
     * @param array          $definition
     */
    public function testSaveRecord(EntryTypeModel $entryType, array $definition)
    {
        Craft::$app->sections->expects($this->exactly(1))
                             ->method('saveEntryType')
                             ->with($entryType)
                             ->willReturn(true);

        $result = $this->converter->saveRecord($entryType, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideEntryTypes
     *
     * @param EntryTypeModel $entryType
     */
    public function testDeleteRecord(EntryTypeModel $entryType)
    {
        Craft::$app->sections->expects($this->exactly(1))
                             ->method('deleteEntryType')
                             ->with($entryType);

        $this->converter->deleteRecord($entryType);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideEntryTypes()
    {
        $mockEntryType = $this->getMockEntryType(1);

        return [
            'valid entryType' => [
                'entryType' => $mockEntryType,
                'definition' => $this->getMockEntryTypeDefinition($mockEntryType),
                'defaultAttributes' => ['sectionId' => 1],
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param EntryTypeModel $mockEntryType
     *
     * @return array
     */
    private function getMockEntryTypeDefinition(EntryTypeModel $mockEntryType)
    {
        return [
            'class' => get_class($mockEntryType),
            'attributes' => [
                'name' => 'entryTypeName'.$mockEntryType->id,
                'handle' => 'entryTypeHandle'.$mockEntryType->id,
                'hasTitleField' => true,
                'titleLabel' => 'Title',
                'titleFormat' => null,
            ],
            'fieldLayout' => $this->getMockFieldLayoutDefinition($mockEntryType->getFieldLayout()),
        ];
    }

    /**
     * Get mock field layout definition.
     *
     * @param FieldLayout $fieldLayout
     *
     * @return array
     */
    private function getMockFieldLayoutDefinition(FieldLayout $fieldLayout)
    {
        $tabsDef = [];
        foreach ($fieldLayout->getTabs() as $tab) {
            $tabsDef[$tab->name] = [];
            foreach ($tab->getFields() as $field) {
                $tabsDef[$tab->name][$field->handle] = $field->required;
            }
        }

        return [
            'type' => Entry::class,
            'tabs' => $tabsDef,
        ];
    }

    /**
     * @param int $entryTypeId
     *
     * @return Mock|EntryTypeModel
     */
    private function getMockEntryType(int $entryTypeId)
    {
        $mockEntryType = $this->getMockBuilder(EntryTypeModel::class)
                              ->setMethods(['getFieldLayout'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $mockEntryType->id = $entryTypeId;
        $mockEntryType->fieldLayoutId = $entryTypeId;
        $mockEntryType->handle = 'entryTypeHandle'.$entryTypeId;
        $mockEntryType->name = 'entryTypeName'.$entryTypeId;

        $mockField = $this->getMockField($entryTypeId);

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();
        $mockFieldLayout->type = Entry::class;

        $mockFieldLayoutTab = $this->getMockBuilder(FieldLayoutTab::class)->getMock();
        $mockFieldLayoutTab->name = 'Content';

        $mockFieldLayout->expects($this->any())
                        ->method('getTabs')
                        ->willReturn([$mockFieldLayoutTab]);

        $mockFieldLayoutTab->expects($this->any())
                           ->method('getFields')
                           ->willReturn([$mockField]);

        $mockEntryType->expects($this->any())
                   ->method('getFieldLayout')
                   ->willReturn($mockFieldLayout);

        return $mockEntryType;
    }

    /**
     * Get a mock field.
     *
     * @param int $fieldId
     *
     * @return Mock|FieldModel
     */
    private function getMockField(int $fieldId)
    {
        $mockField = $this->getMockbuilder(FieldModel::class)
                         ->setMethods([])
                         ->getMock();

        $mockField->id = $fieldId;
        $mockField->handle = 'field'.$fieldId;
        $mockField->required = true;

        return $mockField;
    }
}
