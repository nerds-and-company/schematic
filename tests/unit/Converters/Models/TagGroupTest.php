<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\TagGroup as TagGroupModel;
use Codeception\Test\Unit;

/**
 * Class TagGroupTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TagGroupTest extends Unit
{
    /**
     * @var TagGroup
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
        $this->converter = new TagGroup();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideTagGroups
     *
     * @param TagGroupModel $group
     * @param array         $definition
     */
    public function testGetRecordDefinition(TagGroupModel $group, array $definition)
    {
        $result = $this->converter->getRecordDefinition($group);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideTagGroups
     *
     * @param TagGroupModel $group
     * @param array         $definition
     */
    public function testSaveRecord(TagGroupModel $group, array $definition)
    {
        Craft::$app->tags->expects($this->exactly(1))
                         ->method('saveTagGroup')
                         ->with($group)
                         ->willReturn(true);

        $result = $this->converter->saveRecord($group, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideTagGroups
     *
     * @param TagGroupModel $group
     */
    public function testDeleteRecord(TagGroupModel $group)
    {
        Craft::$app->tags->expects($this->exactly(1))
                         ->method('deleteTagGroupById')
                         ->with($group->id);

        $this->converter->deleteRecord($group);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideTagGroups()
    {
        $mockTagGroup = $this->getMockTagGroup(1);

        return [
            'valid tag group' => [
                'group' => $mockTagGroup,
                'definition' => $this->getMockTagGroupDefinition($mockTagGroup),
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param int $tagGroupId
     *
     * @return TagGroupModel
     */
    private function getMockTagGroup(int $tagGroupId)
    {
        return new TagGroupModel([
            'id' => $tagGroupId,
            'handle' => 'tagGroupHandle'.$tagGroupId,
            'name' => 'tagGroupName'.$tagGroupId,
        ]);
    }

    /**
     * @param TagGroupModel $tagGroup
     *
     * @return array
     */
    private function getMockTagGroupDefinition(TagGroupModel $tagGroup)
    {
        return [
          'class' => get_class($tagGroup),
          'attributes' => [
              'name' => 'tagGroupName'.$tagGroup->id,
              'handle' => 'tagGroupHandle'.$tagGroup->id,
          ],
        ];
    }
}
