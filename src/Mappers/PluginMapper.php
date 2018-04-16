<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MapperInterface;
use yii\base\Component as BaseComponent;

/**
 * Schematic Plugin Mapper.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PluginMapper extends BaseComponent implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(array $plugins): array
    {
        $pluginDefinitions = [];
        foreach ($plugins as $handle => $pluginInfo) {
            $pluginDefinitions[$handle] = $this->getPluginDefinition($handle, $pluginInfo);
        }
        ksort($pluginDefinitions);

        return $pluginDefinitions;
    }

    /**
     * @param string $handle
     * @param array  $pluginInfo
     *
     * @return array
     */
    private function getPluginDefinition(string $handle, array $pluginInfo): array
    {
        $settings = null;
        $plugin = Craft::$app->plugins->getPlugin($handle);
        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        return [
          'isEnabled' => $pluginInfo['isEnabled'],
          'isInstalled' => $pluginInfo['isInstalled'],
          'settings' => $settings ? $settings->attributes : [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $pluginDefinitions, array $plugins): array
    {
        $imported = [];
        foreach ($pluginDefinitions as $handle => $definition) {
            if (!array_key_exists($handle, $plugins)) {
                Schematic::error(' - Plugin info not found for '.$handle.', make sure it is installed with composer');
                continue;
            }
            if (!$definition['isInstalled']) {
                continue;
            }
            Schematic::info('- Installing plugin '.$handle);
            $pluginInfo = $plugins[$handle];
            if ($this->savePlugin($handle, $definition, $pluginInfo)) {
                $imported[] = Craft::$app->plugins->getPlugin($handle);
            }
            unset($plugins[$handle]);
        }

        if (Schematic::$force) {
            foreach (array_keys($plugins) as $handle) {
                if ($plugins[$handle]['isInstalled']) {
                    Schematic::info('- Uninstalling plugin '.$handle);
                    Craft::$app->plugins->uninstallPlugin($handle);
                }
            }
        }

        return $imported;
    }

    /**
     * Install, enable, disable and/or update plugin.
     *
     * @param string $handle
     * @param array  $definition
     * @param array  $pluginInfo
     *
     * @return bool
     */
    private function savePlugin(string $handle, array $definition, array $pluginInfo): bool
    {
        if (!$pluginInfo['isInstalled']) {
            Craft::$app->plugins->installPlugin($handle);
        }
        if ($definition['isEnabled']) {
            Craft::$app->plugins->enablePlugin($handle);
        } else {
            Craft::$app->plugins->disablePlugin($handle);
        }
        $plugin = Craft::$app->plugins->getPlugin($handle);

        if ($plugin && $plugin->getSettings()) {
            return Craft::$app->plugins->savePluginSettings($plugin, $definition['settings']);
        }

        return false;
    }
}
