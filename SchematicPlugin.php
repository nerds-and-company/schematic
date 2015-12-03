<?php

namespace Craft;

/**
 * Schematic Plugin.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class SchematicPlugin extends BasePlugin
{
    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Schematic');
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.4.1';
    }

    /**
     * Get plugin developer.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Nerds & Company';
    }

    /**
     * Get plugin developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://www.nerds.company';
    }

    /**
     * Initialize the autoloader
     */
    public function init()
    {
        $autoloadPath = CRAFT_BASE_PATH.'../vendor/autoload.php';
        if(!class_exists('Symfony\Component\Yaml\Yaml') && file_exists($autoloadPath)){
            require_once $autoloadPath;
        }

    }
}
