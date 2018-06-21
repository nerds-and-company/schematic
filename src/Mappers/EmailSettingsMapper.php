<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use yii\base\Component as BaseComponent;

/**
 * Schematic Email Settings Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class EmailSettingsMapper extends BaseComponent implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(array $settings = []): array
    {
        $settings = Craft::$app->systemSettings->getSettings('email');

        return [
            'settings' => $settings,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $emailSettings, array $settings = []): array
    {
        if (array_key_exists('settings', $emailSettings)) {
            Schematic::info('- Saving email settings');

            if (!Craft::$app->systemSettings->saveSettings('email', $emailSettings['settings'])) {
                Schematic::warning('- Couldn’t save email settings.');
            }
        }

        return [];
    }
}
