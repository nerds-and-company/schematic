<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use yii\base\Component as BaseComponent;
use craft\models\Info;

/**
 * Schematic General Settings Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GeneralSettingsMapper extends BaseComponent implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(array $settings = []): array
    {
        $info = Craft::$app->getInfo();

        return [
            'settings' => [
                'edition' => $info->edition,
                'timezone' => $info->timezone,
                'name' => $info->name,
                'on' => $info->on,
                'maintenance' => $info->maintenance,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $generalSettings, array $settings = []): array
    {
        if (array_key_exists('settings', $generalSettings)) {
            Schematic::info('- Saving general settings');

            $record = Craft::$app->getInfo();
            $record->setAttributes($generalSettings['settings']);

            if (!Craft::$app->saveInfo($record)) {
                Schematic::warning('- Couldn’t save general settings.');
            }
        }

        return [];
    }
}
