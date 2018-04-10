<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\Plugin;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use yii\base\Component as BaseComponent;

/**
 * Schematic Plugins Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Plugins extends BaseComponent implements MappingInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(array $plugins = null): array
    {
        $plugins = Craft::$app->plugins->getAllPlugins();
        $pluginDefinitions = [];

        foreach ($plugins as $plugin) {
            $handle = preg_replace('/^Craft\\\\(.*?)Plugin$/', '$1', get_class($plugin));
            $pluginDefinitions[$handle] = $this->getPluginDefinition($plugin);
        }
        ksort($pluginDefinitions);

        return $pluginDefinitions;
    }

    /**
     * @param Plugin $plugin
     *
     * @return array
     */
    private function getPluginDefinition(Plugin $plugin)
    {
        return [
            'isInstalled' => $plugin->isInstalled,
            'settings' => $plugin->getSettings()->attributes,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $pluginDefinitions, array $plugins): array
    {
        Schematic::warning('Import of plugins is not yet implemented');
        //TODO rebuild plugins import
        return $plugins;
    }
}
