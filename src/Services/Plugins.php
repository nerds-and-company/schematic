<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\Exception;
use Craft\BasePlugin;

/**
 * Schematic Plugins Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Plugins extends Base
{
    /**
     * @return PluginsService
     */
    protected function getPluginService()
    {
        return Craft::app()->plugins;
    }

    /**
     * @return UpdatesService
     */
    protected function getUpdatesService()
    {
        return Craft::app()->updates;
    }

    /**
     * Installs plugin by handle.
     *
     * @param string $handle
     */
    protected function installPluginByHandle($handle)
    {
        Craft::log(Craft::t('Installing plugin {handle}', ['handle' => $handle]));

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
            $this->addError(Craft::t("Plugin {handle} could not be found, make sure it's files are located in the plugins folder", ['handle' => $handle]));
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
     * @param BasePlugin $plugin
     *
     * @return array
     */
    private function getPluginDefinition(BasePlugin $plugin)
    {
        return [
            'isInstalled' => $plugin->isInstalled,
            'isEnabled' => $plugin->isEnabled,
            'settings' => $plugin->getSettings()->attributes,
        ];
    }

    /**
     * @param array $pluginDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $pluginDefinitions, $force = false)
    {
        Craft::log(Craft::t('Updating Craft'));
        if ($this->getUpdatesService()->isCraftDbMigrationNeeded()) {
            $result = $this->getUpdatesService()->updateDatabase('craft');
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
        }

        Craft::log(Craft::t('Importing Plugins'));
        foreach ($pluginDefinitions as $handle => $pluginDefinition) {
            Craft::log(Craft::t('Applying definitions for {handle}', ['handle' => $handle]));

            if ($plugin = $this->getPlugin($handle)) {
                if ($pluginDefinition['isInstalled']) {
                    $isNewPlugin = !$plugin->isInstalled;
                    if ($isNewPlugin) {
                        $this->installPluginByHandle($handle);
                    }

                    $this->togglePluginByHandle($handle, $pluginDefinition['isEnabled']);

                    if (!$isNewPlugin && $plugin->isEnabled) {
                        $this->getUpdatesService()->updateDatabase($handle);
                    }

                    if (array_key_exists('settings', $pluginDefinition)) {
                        Craft::log(Craft::t('Saving plugin settings for {handle}', ['handle' => $handle]));

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
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Plugins'));

        $plugins = $this->getPluginService()->getPlugins(false);
        $pluginDefinitions = [];

        foreach ($plugins as $plugin) {
            $handle = preg_replace('/^Craft\\\\(.*?)Plugin$/', '$1', get_class($plugin));
            $pluginDefinitions[$handle] = $this->getPluginDefinition($plugin);
        }
        ksort($pluginDefinitions);

        return $pluginDefinitions;
    }
}
