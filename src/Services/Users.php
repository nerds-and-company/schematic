<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\User;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Users Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Users extends BaseComponent implements MappingInterface
{
    /**
     * Load fieldlayout behavior
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          FieldLayoutBehavior::className(),
        ];
    }

    /**
     * Export user settings
     *
     * @return array
     */
    public function export()
    {
        $settings = Craft::$app->systemSettings->getSettings('users');
        $fieldLayout = Craft::$app->getFields()->getLayoutByType(User::class);
        return [
            'settings' => $settings,
            'fieldLayout' => $this->getFieldLayoutDefinition($fieldLayout),
        ];
    }

    /**
     * Import user settings.
     *
     * @param array $userSettings
     */
    public function import(array $userSettings)
    {
        if (array_key_exists('settings', $userSettings)) {
            Schematic::info('- Saving user settings');
            if (!Craft::$app->systemSettings->saveSettings('users', $userSettings['settings'])) {
                Schematic::warning('- Couldn’t save user settings.');
            }
        }

        if (array_key_exists('fieldLayout', $userSettings)) {
            Schematic::info('- Saving user field layout');
            $fieldLayout = $this->getFieldLayout($userSettings['fieldLayout']);
            $fieldLayout->type = User::class;

            Craft::$app->fields->deleteLayoutsByType(User::class);
            if (!Craft::$app->fields->saveLayout($fieldLayout)) {
                Schematic::warning('- Couldn’t save user field layout.');
            }
        }
    }
}
