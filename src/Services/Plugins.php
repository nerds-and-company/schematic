<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use Craft\Exception;
use Craft\BasePlugin;

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
class Plugins extends Base
{
    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Get all asset transforms
     *
     * @return Plugin[]
     */
    protected function getRecords()
    {
        return Craft::$app->plugins->getAllPlugins();
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Installs plugin by handle.
     *
     * @param string $handle
     */
    protected function installPluginByHandle($handle)
    {
        Craft::info('Installing plugin {handle}', ['handle' => $handle], 'schematic');

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
            $this->addError("Plugin {handle} could not be found, make sure it's files are located in the plugins folder", ['handle' => $handle]);
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
    public function import($force = false, array $pluginDefinitions = null)
    {
        Craft::info('Updating Craft', 'schematic');
        if ($this->getUpdatesService()->isCraftDbMigrationNeeded()) {
            $result = $this->getUpdatesService()->updateDatabase('craft');
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
        }

        Craft::info('Importing Plugins', 'schematic');
        foreach ($pluginDefinitions as $handle => $pluginDefinition) {
            Craft::info('Applying definitions for {handle}', ['handle' => $handle], 'schematic');

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
                        Craft::info('Saving plugin settings for {handle}', ['handle' => $handle], 'schematic');

                        $this->getPluginService()->savePluginSettings($plugin, $pluginDefinition['settings']);
                    }
                } else {
                    $this->uninstallPluginByHandle($handle);
                }
            }
        }

        return $this->getResultModel();
    }
}
