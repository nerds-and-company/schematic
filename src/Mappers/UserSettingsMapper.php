<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use craft\elements\User;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use yii\base\Component as BaseComponent;

/**
 * Schematic User Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserSettingsMapper extends BaseComponent implements MapperInterface
{
    /**
     * Load fieldlayout behavior.
     *
     * @return array
     */
    public function behaviors(): array
    {
        return [
          FieldLayoutBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $settings = []): array
    {
        $settings = Craft::$app->systemSettings->getSettings('users');
        $photoVolumeId = (int) $settings['photoVolumeId'];
        $volume = Craft::$app->volumes->getVolumeById($photoVolumeId);
        unset($settings['photoVolumeId']);
        $settings['photoVolume'] = $volume ? $volume->handle : null;

        $fieldLayout = Craft::$app->fields->getLayoutByType(User::class);

        return [
            'settings' => $settings,
            'fieldLayout' => $this->getFieldLayoutDefinition($fieldLayout),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $userSettings, array $settings = []): array
    {
        $photoVolumeId = null;
        if (array_key_exists('photoVolume', $userSettings['settings']) && null != $userSettings['settings']['photoVolume']) {
            $volume = Craft::$app->volumes->getVolumeByHandle($userSettings['settings']['photoVolume']);
            $photoVolumeId = $volume ? $volume->id : null;
        }
        unset($userSettings['settings']['photoVolume']);

        if (array_key_exists('settings', $userSettings)) {
            Schematic::info('- Saving user settings');
            $userSettings['settings']['photoVolumeId'] = $photoVolumeId;
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

                Schematic::importError($fieldLayout, 'users');
            }
        }

        return [];
    }
}
