<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\console\Application;
use craft\models\UserGroup as UserGroupModel;
use Codeception\Test\Unit;

/**
 * Class UserGroupTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserGroupTest extends Unit
{
    /**
     * @var UserGroups
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $this->converter = new UserGroup();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideUserGroups
     *
     * @param UserGroupModel $group
     * @param array          $definition
     */
    public function testGetRecordDefinition(UserGroupModel $group, array $definition)
    {
        $result = $this->converter->getRecordDefinition($group);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideUserGroups
     *
     * @param UserGroupModel $group
     * @param array          $definition
     */
    public function testSaveRecord(UserGroupModel $group, array $definition)
    {
        Craft::$app->userGroups->expects($this->exactly(1))
                               ->method('saveGroup')
                               ->with($group)
                               ->willReturn(true);

        Craft::$app->userPermissions->expects($this->exactly(1))
                                    ->method('saveGroupPermissions')
                                    ->with($group->id, [])
                                    ->willReturn(true);

        $result = $this->converter->saveRecord($group, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideUserGroups
     *
     * @param UserGroupModel $group
     */
    public function testDeleteRecord(UserGroupModel $group)
    {
        Craft::$app->userGroups->expects($this->exactly(1))
                               ->method('deleteGroupById')
                               ->with($group->id);

        $this->converter->deleteRecord($group);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideUserGroups()
    {
        $mockUserGroup = $this->getMockUserGroup(1);

        return [
            'valid user group' => [
                'group' => $mockUserGroup,
                'definition' => $this->getMockUserGroupDefinition($mockUserGroup),
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param int $userGroupId
     *
     * @return UserGroupModel
     */
    private function getMockUserGroup(int $userGroupId)
    {
        $mockApp = $this->getMockBuilder(Application::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $mockApp->expects($this->exactly(1))
                ->method('requireEdition')
                ->with(Craft::Pro)
                ->willReturn(true);

        Craft::$app = $mockApp;

        $mockUserGroup = $this->getmockBuilder(UserGroupModel::class)
                              ->setMethods(['__toString'])
                              ->getMock();

        $mockUserGroup->id = $userGroupId;
        $mockUserGroup->handle = 'userGroupHandle'.$userGroupId;
        $mockUserGroup->name = 'userGroupName'.$userGroupId;

        return $mockUserGroup;
    }

    /**
     * @param UserGroupModel $userGroup
     *
     * @return array
     */
    private function getMockUserGroupDefinition(UserGroupModel $userGroup)
    {
        return [
          'class' => get_class($userGroup),
          'attributes' => [
              'name' => 'userGroupName'.$userGroup->id,
              'handle' => 'userGroupHandle'.$userGroup->id,
          ],
          'permissions' => [],
        ];
    }
}
