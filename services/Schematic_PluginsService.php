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
class Schematic_PluginsService extends Schematic_AbstractService
{
    /**
     * @return PluginsService
     */
    protected function getPluginService()
    {
        return craft()->plugins;
    }

    /**
     * @return MigrationsService
     */
    protected function getMigrationsService()
    {
        return craft()->migrations;
    }

    /**
     * @return UpdatesService
     */
    protected function getUpdatesService()
    {
        return craft()->updates;
    }

    /**
     * Installs plugin by handle.
     *
     * @param string $handle
     */
    protected function installPluginByHandle($handle)
    {
        try {
            $this->getPluginService()->installPlugin($handle);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Uninstalls plugin by handle.
     *
     * @param $handle
     */
    protected function uninstallPluginByHandle($handle)
    {
        $this->getPluginService()->uninstallPlugin($handle);
    }

    /**
     * Returns plugin by handle.
     *
     * @param string $handle
     *
     * @return BasePlugin|null
     */
    protected function getPlugin($handle)
    {
        $plugin = $this->getPluginService()->getPlugin($handle, false);
        if (!$plugin) {
            $this->addError("Plugin $handle could not be found, make sure it's files are located in the plugins folder");
        }

        return $plugin;
    }

    /**
     * Toggles plugin based on enabled flag.
     *
     * @param string $handle
     * @param bool   $isEnabled
     */
    protected function togglePluginByHandle($handle, $isEnabled)
    {
        if ($isEnabled) {
            $this->getPluginService()->enablePlugin($handle);
        } else {
            $this->getPluginService()->disablePlugin($handle);
        }
    }

    /**
     * Run plugin migrations automatically.
     *
     * @param BasePlugin $plugin
     */
    protected function runMigrations(BasePlugin $plugin)
    {
        if (!$this->getMigrationsService()->runToTop($plugin)) {
            throw new Exception(Craft::t('There was a problem updating your database.'));
        }
        if (!$this->getUpdatesService()->setNewPluginInfo($plugin)) {
            throw new Exception(Craft::t('The update was performed successfully, but there was a problem setting the new info in the plugins table.'));
        }
    }

    /**
     * @param BasePlugin $plugin
     *
     * @return array
     */
    private function getPluginDefinition(BasePlugin $plugin)
    {
        return array(
            'isInstalled'       => $plugin->isInstalled,
            'isEnabled'         => $plugin->isEnabled,
            'settings'          => $plugin->getSettings()->attributes,
        );
    }

    /**
     * @param array $pluginDefinitions
     * @param bool  $force
     *
     * @return Schematic_ResultModel
     */
    public function import(array $pluginDefinitions, $force = false)
    {
        foreach ($pluginDefinitions as $handle => $pluginDefinition) {
            if ($plugin = $this->getPlugin($handle)) {
                if ($pluginDefinition['isInstalled']) {
                    if (!$plugin->isInstalled) {
                        $this->installPluginByHandle($handle);
                    } else {
                        $this->runMigrations($plugin);
                    }

                    $this->togglePluginByHandle($handle, $pluginDefinition['isEnabled']);

                    if (array_key_exists('settings', $pluginDefinition)) {
                        $this->getPluginService()->savePluginSettings($plugin, $pluginDefinition['settings']);
                    }
                } else {
                    $this->uninstallPluginByHandle($handle);
                }
            }
        }

        return $this->getResultModel();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = array())
    {
        $plugins = $this->getPluginService()->getPlugins(false);
        $pluginDefinitions = array();

        foreach ($plugins as $handle => $plugin) {
            $pluginDefinitions[$handle] = $this->getPluginDefinition($plugin);
        }
        ksort($pluginDefinitions);

        return $pluginDefinitions;
    }
}
