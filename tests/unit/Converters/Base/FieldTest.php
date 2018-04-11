<?php

namespace NerdsAndCompany\Schematic\Converters\Base;

use Craft;
use craft\base\Field as FieldModel;
use Codeception\Test\Unit;

/**
 * Class FieldTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class FieldTest extends Unit
{
    /**
     * @var Fields
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $this->converter = new Field();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideFields
     *
     * @param FieldModel $field
     * @param array      $definition
     */
    public function testGetRecordDefinition(FieldModel $field, array $definition)
    {
        $result = $this->converter->getRecordDefinition($field);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideFields
     *
     * @param FieldModel $field
     * @param array      $definition
     * @param string     $groupStatus existing|new|invalid
     */
    public function testSaveRecord(FieldModel $field, array $definition, string $groupStatus)
    {
        Craft::$app->fields->expects($this->exactly(1))
                          ->method('getAllGroups')
                          ->willReturn([$this->getMockFieldGroup(1)]);

        Craft::$app->fields->expects($this->exactly($groupStatus == 'existing' ? 0 : 1))
                          ->method('saveGroup')
                          ->willReturn($groupStatus !== 'invalid');

        Craft::$app->fields->expects($this->exactly(1))
                          ->method('saveField')
                          ->with($field)
                          ->willReturn(true);

        $result = $this->converter->saveRecord($field, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideFields
     *
     * @param FieldModel $field
     */
    public function testDeleteRecord(FieldModel $field)
    {
        Craft::$app->fields->expects($this->exactly(1))
                            ->method('deleteField')
                            ->with($field);

        $this->converter->deleteRecord($field);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideFields()
    {
        $mockField1 = $this->getMockField(1, 1);
        $mockField2 = $this->getMockField(1, 2);

        return [
            'valid field existing group' => [
                'field' => $mockField1,
                'definition' => $this->getMockFieldDefinition($mockField1),
                'groupStatus' => 'existing',
            ],
            'valid field new group' => [
                'field' => $mockField2,
                'definition' => $this->getMockFieldDefinition($mockField2),
                'groupStatus' => 'new',
            ],
            'valid field invalid group' => [
                'field' => $mockField2,
                'definition' => $this->getMockFieldDefinition($mockField2),
                'groupStatus' => 'invalid',
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param FieldModel $mockField
     *
     * @return array
     */
    private function getMockFieldDefinition(FieldModel $mockField)
    {
        return [
            'class' => get_class($mockField),
            'attributes' => [
                'name' => 'fieldName'.$mockField->id,
                'handle' => 'fieldHandle'.$mockField->id,
                'instructions' => null,
                'translationMethod' => 'none',
                'translationKeyFormat' => null,
                'oldHandle' => null,
                'columnPrefix' => null,
                'required' => false,
                'sortOrder' => null,
            ],
            'group' => $mockField->group->name,
        ];
    }

    /**
     * @param int $fieldId
     * @param int $groupId
     *
     * @return Mock|FieldModel
     */
    private function getMockField(int $fieldId, int $groupId)
    {
        $mockField = $this->getMockBuilder(FieldModel::class)
                           ->setMethods(['getGroup'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockField->id = $fieldId;
        $mockField->groupId = $fieldId;
        $mockField->handle = 'fieldHandle'.$fieldId;
        $mockField->name = 'fieldName'.$fieldId;

        $mockField->expects($this->any())
                 ->method('getGroup')
                 ->willReturn($this->getMockFieldGroup($groupId));

        return $mockField;
    }

    /**
     * Get a mock field group.
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
}
