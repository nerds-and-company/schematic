<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\UserModel;
use Craft\ElementType;

/**
 * Schematic Users Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Users extends Base
{
    /**
     * Export user settings.
     *
     * @param UserModel[] $users
     *
     * @return array
     */
    public function export(array $users = [])
    {
        Craft::log(Craft::t('Exporting Users'));

        return $this->getUsersDefinition(new UserModel());
    }

    /**
     * Get users definition.
     *
     * @param UserModel $user
     *
     * @return array
     */
    private function getUsersDefinition(UserModel $user)
    {
        return [
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($user->getFieldLayout()),
        ];
    }

    /**
     * Attempt to import user settings.
     *
     * @param array $user_settings
     * @param bool  $force         If set to true user settings not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $user_settings, $force = true)
    {
        Craft::log(Craft::t('Importing Users'));

        // always delete existing fieldlayout first
        Craft::app()->fields->deleteLayoutsByType(ElementType::User);

        if (isset($user_settings['fieldLayout'])) {
            $fieldLayoutDefinition = (array) $user_settings['fieldLayout'];
        } else {
            $fieldLayoutDefinition = [];
        }

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($fieldLayoutDefinition);
        $fieldLayout->type = ElementType::User;

        if (!Craft::app()->fields->saveLayout($fieldLayout)) {  // Save fieldlayout via craft
            $this->addErrors($fieldLayout->getAllErrors());
        }

        return $this->getResultModel();
    }
}
