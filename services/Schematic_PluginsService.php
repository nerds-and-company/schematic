<?php

namespace Craft;

/**
 * Schematic Plugins Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_PluginsService extends BaseApplicationComponent
{
    /**
     * Import plugins.
     *
     * @param array $pluginDefinitions
     *
     * @return Schematic_ResultModel
     */
    public function import(array $pluginDefinitions)
    {
        $result = new Schematic_ResultModel();
        foreach ($pluginDefinitions as $handle => $pluginDefinition) {
            $plugin = craft()->plugins->getPlugin($handle, false);

            if (!$plugin) {
                return $result->error("Plugin $handle could not be found, make sure it's files are located in the plugins folder");
            }

            if ($pluginDefinition['isInstalled']) {
                try {
                    craft()->plugins->installPlugin($handle);
                } catch (\Exception $e) {
                    echo "An error occurred while installing plugin $handle, continuing anyway".PHP_EOL;
                }

                if ($pluginDefinition['isEnabled']) {
                    craft()->plugins->enablePlugin($handle);
                } else {
                    craft()->plugins->disablePlugin($handle);
                }

                craft()->plugins->savePluginSettings($plugin, $pluginDefinition['settings']);
            } else {
                craft()->plugins->uninstallPlugin($handle);
            }
        }

        return $result;
    }

    /**
     * Export plugins.
     *
     * @return array
     */
    public function export()
    {
        $plugins = craft()->plugins->getPlugins(false);
        $pluginDefinitions = array();

        foreach ($plugins as $handle => $plugin) {
            $pluginDefinitions[$handle] = $this->getPluginDefinition($plugin);
        }

        ksort($pluginDefinitions);

        return $pluginDefinitions;
    }

    /**
     * Get plugin definition.
     *
     * @param BasePlugin $plugin
     *
     * @return array
     */
    private function getPluginDefinition(BasePlugin $plugin)
    {
        return array(
            'isInstalled' => $plugin->isInstalled,
            'isEnabled' => $plugin->isEnabled,
        );
    }
}
