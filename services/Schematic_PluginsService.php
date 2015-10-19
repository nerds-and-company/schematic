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
     * @var Schematic_ResultModel
     */
    protected $resultModel;


    /**
     * Constructor to setup result model
     */
    public function __construct()
    {
        $this->resultModel = new Schematic_ResultModel();
    }

    /**
     * @return PluginsService
     */
    protected function getPluginService()
    {
        return craft()->plugins;
    }

    /**
     * Installs plugin by handle
     * @param string $handle
     */
    protected function installPluginByHandle($handle)
    {
        try {
            $this->getPluginService()->installPlugin($handle);
        } catch (\Exception $e) {
            $this->addError("An error occurred while installing plugin $handle, continuing anyway");
        }
    }

    /**
     * Uninstalls plugin by handle
     * @param $handle
     */
    protected function uninstallPluginByHandle($handle)
    {
        $this->getPluginService()->uninstallPlugin($handle);
    }

    /**
     * Returns plugin by handle
     * @param string $handle
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
     * Toggles plugin based on enabled flag
     * @param string $handle
     * @param bool $isEnabled
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
     * Add error to errors collection
     * @param $message
     */
    private function addError($message)
    {
        $this->resultModel->addError('errors', $message);
    }

    /**
     * @param BasePlugin $plugin
     * @return array
     */
    private function getPluginDefinition(BasePlugin $plugin)
    {
        return array(
            'isInstalled'       => $plugin->isInstalled,
            'isEnabled'         => $plugin->isEnabled,
            'settings'          => $plugin->getSettings()->attributes
        );
    }

    /**
     * @param array $pluginDefinitions
     * @return Schematic_ResultModel
     */
    public function import(array $pluginDefinitions)
    {
        foreach ($pluginDefinitions as $handle => $pluginDefinition) {
            if($plugin = $this->getPlugin($handle)) {
                if ($pluginDefinition['isInstalled']) {
                    $this->installPluginByHandle($handle);

                    $this->togglePluginByHandle($handle, $pluginDefinition['isEnabled']);

                    if(array_key_exists('settings', $pluginDefinition)){
                        $this->getPluginService()->savePluginSettings($plugin, $pluginDefinition['settings']);
                    }
                } else {
                    $this->uninstallPluginByHandle($handle);
                }
            }
        }

        return $this->resultModel;
    }

    /**
     * @return array
     */
    public function export()
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
