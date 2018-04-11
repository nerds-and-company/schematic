<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Field as FieldModel;
use craft\models\EntryType as EntryTypeModel;
use craft\models\FieldLayout;
use craft\entryTypes\Local;
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
     * @var EntryTypes
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
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
            'local entryType' => [
                'entryType' => $mockEntryType,
                'definition' => $this->getMockEntryTypeDefinition($mockEntryType),
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
        $fieldDefs = [];
        foreach ($mockEntryType->getFieldLayout()->getFields() as $field) {
            $fieldDefs[$field->handle] = $field->required;
        }

        return [
            'class' => get_class($mockEntryType),
            'attributes' => [
                'name' => 'entryTypeName'.$mockEntryType->id,
                'handle' => 'entryTypeHandle'.$mockEntryType->id,
                'hasTitleField' => true,
                'titleLabel' => 'Title',
                'titleFormat' => null,
            ],
            'fieldLayout' => [
                'fields' => $fieldDefs,
            ],
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

        $mockField = $this->getMockbuilder(FieldModel::class)->getMock();
        $mockField->id = $entryTypeId;
        $mockField->handle = 'field'.$entryTypeId;
        $mockField->required = true;

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();

        $mockFieldLayout->expects($this->any())
                        ->method('getFields')
                        ->willReturn([$mockField]);

        $mockEntryType->expects($this->any())
                   ->method('getFieldLayout')
                   ->willReturn($mockFieldLayout);

        return $mockEntryType;
    }
}
