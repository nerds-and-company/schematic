<?php

namespace NerdsAndCompany\Schematic\Converters\Base;

use Craft;
use craft\base\Field as FieldModel;
use craft\fields\Categories as CategoriesField;
use craft\fields\PlainText as PlainTextField;
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
     * @var Field
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
        if ($field instanceof CategoriesField) {
            $mockCategoryGroup = $this->getMockCategoryGroup(1);

            Craft::$app->categories->expects($this->any())
                                   ->method('getGroupById')
                                   ->with($field->group->id)
                                   ->willReturn($mockCategoryGroup);
        }

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

        Craft::$app->fields->expects($this->exactly('existing' == $groupStatus ? 0 : 1))
                          ->method('saveGroup')
                          ->willReturn('invalid' !== $groupStatus);

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

    /**
     * @dataProvider provideFields
     *
     * @param FieldModel $field
     * @param array      $definition
     */
    public function testSetRecordAttributes(FieldModel $field, array $definition)
    {
        $newField = new PlainTextField();

        if ($field instanceof CategoriesField) {
            $newField = new CategoriesField();
            $mockCategoryGroup = $this->getMockCategoryGroup(1);

            Craft::$app->categories->expects($this->any())
                                   ->method('getGroupByHandle')
                                   ->with('categoryGroupHandle'.$field->group->id)
                                   ->willReturn($mockCategoryGroup);
        }

        $this->converter->setRecordAttributes($newField, $definition, []);

        $this->assertSame($field->name, $newField->name);
        $this->assertSame($field->handle, $newField->handle);

        if ($field instanceof CategoriesField) {
            $this->assertSame($field->source, $newField->source);
        }
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
        $mockField3 = $this->getMockCategoriesField(1, 1);

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
            'categories field existing group' => [
                'field' => $mockField3,
                'definition' => $this->getMockCategoriesFieldDefinition($mockField3),
                'groupStatus' => 'existing',
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
     * @param CategoriesField $mockField
     *
     * @return array
     */
    private function getMockCategoriesFieldDefinition(CategoriesField $mockField)
    {
        $fieldDefinition = $this->getMockFieldDefinition($mockField);

        $fieldDefinition['attributes'] = array_merge([
            'branchLimit' => null,
            'sources' => '*',
            'source' => 'group:categoryGroupHandle1',
            'targetSiteId' => null,
            'viewMode' => null,
            'limit' => null,
            'selectionLabel' => null,
            'localizeRelations' => false,
            'allowMultipleSources' => true,
            'allowLimit' => true,
        ], $fieldDefinition['attributes']);

        return $fieldDefinition;
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

        $this->setMockFieldAttributes($mockField, $fieldId, $groupId);

        return $mockField;
    }

    /**
     * @param int $fieldId
     * @param int $groupId
     *
     * @return Mock|Categories
     */
    private function getMockCategoriesField(int $fieldId, int $groupId)
    {
        $mockField = $this->getMockBuilder(CategoriesField::class)
                          ->setMethods(['getGroup'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $this->setMockFieldAttributes($mockField, $fieldId, $groupId);

        $mockField->source = 'group:'.$groupId;

        return $mockField;
    }

    /**
     * @param Mock|FieldModel $mockField
     * @param int             $fieldId
     * @param int             $groupId
     */
    private function setMockFieldAttributes(FieldModel &$mockField, int $fieldId, int $groupId)
    {
        $mockField->id = $fieldId;
        $mockField->groupId = $fieldId;
        $mockField->handle = 'fieldHandle'.$fieldId;
        $mockField->name = 'fieldName'.$fieldId;

        $mockField->expects($this->any())
                  ->method('getGroup')
                  ->willReturn($this->getMockFieldGroup($groupId));
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
                          ->getMock();

        $mockGroup->id = $groupId;
        $mockGroup->name = 'fieldGroup'.$groupId;

        return $mockGroup;
    }

    /**
     * @param int $groupId
     *
     * @return Mock|CategoryGroup
     */
    private function getMockCategoryGroup(int $groupId)
    {
        $mockCategoryGroup = $this->getMockBuilder(CategoryGroup::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $mockCategoryGroup->id = $groupId;
        $mockCategoryGroup->handle = 'categoryGroupHandle'.$groupId;

        return $mockCategoryGroup;

        Craft::$app->categories->expects($this->any())
                               ->method('getGroupByHandle')
                               ->with('categoryGroupHandle'.$groupId)
                               ->willReturn($mockCategoryGroup);
    }
}
