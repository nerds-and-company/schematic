<?php

namespace NerdsAndCompany\Schematic\Console;

use Craft\Craft;
use Craft\Logger;
use CConsoleApplication as Base;
use NerdsAndCompany\Schematic\Behaviors\Schematic;
use NerdsAndCompany\Schematic\Services as Service;

/**
 * Schematic Console App.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class App extends Base
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $componentAliases;

    /**
     * @var
     */
    private $_pendingEvents;

    /**
     * @var
     */
    private $_editionComponents;

    // Public Methods
    // =========================================================================

    /**
     * Initializes the console app by creating the command runner.
     */
    public function init()
    {
        // Set default timezone to UTC
        date_default_timezone_set('UTC');

        // Import all the built-in components
        foreach ($this->componentAliases as $alias) {
            Craft::import($alias);
        }

        // Attach our Craft app behavior.
        $this->attachBehavior('SchematicBehavior', new Schematic());

        // Attach our own custom Logger
        Craft::setLogger(new Logger());

        // If there is a custom appId set, apply it here.
        if ($appId = $this->config->get('appId')) {
            $this->setId($appId);
        }

        // Initialize Cache and LogRouter right away (order is important)
        $this->getComponent('cache');
        $this->getComponent('log');

        // So we can try to translate Yii framework strings
        $this->coreMessages->attachEventHandler('onMissingTranslation', ['Craft\LocalizationHelper', 'findMissingTranslation']);

        // Set our own custom runtime path.
        $this->setRuntimePath(Craft::app()->path->getRuntimePath());

        // No need for these.
        Craft::app()->log->removeRoute('WebLogRoute');
        Craft::app()->log->removeRoute('ProfileLogRoute');

        // Set the edition components
        $this->_setEditionComponents();

        // Install Craft if needed
        if (!$this->isInstalled()) {
            $this->_installCraft();
        }

        // Set the schematic components
        $this->_setSchematicComponents();

        // Call parent::init() before the plugin console command logic so the command runner gets initialized
        parent::init();

        // Load the plugins
        Craft::app()->plugins->loadPlugins();

        // Validate some basics on the database configuration file.
        Craft::app()->validateDbConfigFile();

        // Add commands
        Craft::app()->commandRunner->commands = [];
        Craft::app()->commandRunner->addCommands(__DIR__.'/../ConsoleCommands/');
    }

    /**
     * Returns the target application language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->asa('SchematicBehavior')->getLanguage();
    }

    /**
     * Sets the target application language.
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->asa('SchematicBehavior')->setLanguage($language);
    }

    /**
     * Returns the system time zone.  Note that this method cannot be in {@link AppBehavior}, because Yii will check
     * {@link \CApplication::getTimeZone()} instead.
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->asa('SchematicBehavior')->getTimezone();
    }

    /**
     * Attaches an event handler, or remembers it for later if the component has not been initialized yet.
     *
     * The event should be identified in a `serviceHandle.eventName` format. For example, if you want to add an event
     * handler for {@link EntriesService::onSaveEntry()}, you would do this:
     *
     * ```php
     * Craft::app()->on('entries.saveEntry', function(Event $event) {
     *     // ...
     * });
     * ```
     *
     * Note that the actual event name (`saveEntry`) does not need to include the “`on`”.
     *
     * By default, event handlers will not get attached if Craft is current in the middle of updating itself or a
     * plugin. If you want the event to fire even in that condition, pass `true` to the $evenDuringUpdates argument.
     *
     * @param string $event   The event to listen for
     * @param mixed  $handler The event handler
     */
    public function on($event, $handler)
    {
        list($componentId, $eventName) = explode('.', $event, 2);

        $component = $this->getComponent($componentId, false);

        // Normalize the event name
        if (strncmp($eventName, 'on', 2) !== 0) {
            $eventName = 'on'.ucfirst($eventName);
        }

        if ($component) {
            $component->$eventName = $handler;
        } else {
            $this->_pendingEvents[$componentId][$eventName][] = $handler;
        }
    }

    /**
     * Returns whether we are executing in the context on a console app.
     *
     * @return bool
     */
    public function isConsole()
    {
        return true;
    }

    /**
     * Returns the target application theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return null;
    }


    /**
     * Override getComponent() so we can attach any pending events if the component is getting initialized as well as
     * do some special logic around creating the `Craft::app()->db` application component.
     *
     * @param string $id
     * @param bool   $createIfNull
     *
     * @return mixed
     */
    public function getComponent($id, $createIfNull = true)
    {
        $component = parent::getComponent($id, false);

        if (!$component && $createIfNull) {
            if ($id === 'db') {
                $dbConnection = $this->asa('SchematicBehavior')->createDbConnection();
                $this->setComponent('db', $dbConnection);
            }

            $component = parent::getComponent($id, true);
            $this->_attachEventListeners($id);
        }

        return $component;
    }

    /**
     * Sets the application components.
     *
     * @param      $components
     * @param bool $merge
     */
    public function setComponents($components, $merge = true)
    {
        if (isset($components['editionComponents'])) {
            $this->_editionComponents = $components['editionComponents'];
            unset($components['editionComponents']);
        }

        parent::setComponents($components, $merge);
    }

    /**
     * @todo Remove for Craft 3
     *
     * @param int    $code    The level of the error raised
     * @param string $message The error message
     * @param string $file    The filename that the error was raised in
     * @param int    $line    The line number the error was raised at
     */
    public function handleError($code, $message, $file, $line)
    {
        // PHP 7 turned some E_STRICT messages to E_WARNINGs. Code 2 is for all warnings
        // and since there are no messages specific codes we have to parse the string for what
        // we're looking for. Lame, but it works since all PHP error messages are always in English.
        // https://stackoverflow.com/questions/11556375/is-there-a-way-to-localize-phps-error-output
        if (version_compare(PHP_VERSION, '7', '>=') && $code === 2 && strpos($message, 'should be compatible with') !== false) {
            return;
        }

        parent::handleError($code, $message, $file, $line);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return ConsoleCommandRunner
     */
    protected function createCommandRunner()
    {
        return new CommandRunner();
    }

    // Private Methods
    // =========================================================================

    /**
     * Attaches any pending event listeners to the newly-initialized component.
     *
     * @param string $componentId
     */
    private function _attachEventListeners($componentId)
    {
        if (isset($this->_pendingEvents[$componentId])) {
            $component = $this->getComponent($componentId, false);

            if ($component) {
                foreach ($this->_pendingEvents[$componentId] as $eventName => $handlers) {
                    foreach ($handlers as $handler) {
                        $component->$eventName = $handler;
                    }
                }
            }
        }
    }

    /**
     * Sets the edition components.
     */
    private function _setEditionComponents()
    {
        // Set the appropriate edition components
        if (isset($this->_editionComponents)) {
            foreach ($this->_editionComponents as $edition => $editionComponents) {
                if ($this->getEdition() >= $edition) {
                    $this->setComponents($editionComponents);
                }
            }

            unset($this->_editionComponents);
        }
    }

    /**
     * Sets the schematic components.
     */
    private function _setSchematicComponents()
    {
        $components = [
            'schematic' => [
                'class' => Service\Schematic::class,
            ],
            'schematic_locales' => [
                'class' => Service\Locales::class,
            ],
            'schematic_assetSources' => [
                'class' => Service\AssetSources::class,
            ],
            'schematic_assetTransforms' => [
                'class' => Service\AssetTransforms::class,
            ],
            'schematic_fields' => [
                'class' => Service\Fields::class,
            ],
            'schematic_globalSets' => [
                'class' => Service\GlobalSets::class,
            ],
            'schematic_plugins' => [
                'class' => Service\Plugins::class,
            ],
            'schematic_sections' => [
                'class' => Service\Sections::class,
            ],
            'schematic_userGroups' => [
                'class' => Service\UserGroups::class,
            ],
            'schematic_users' => [
                'class' => Service\Users::class,
            ],
            'schematic_categoryGroups' => [
                'class' => Service\CategoryGroups::class,
            ],
            'schematic_tagGroups' => [
                'class' => Service\TagGroups::class,
            ],
            'schematic_sources' => [
                'class' => Service\Sources::class,
            ],
        ];

        // Element index settings are supported from Craft 2.5
        if (version_compare(CRAFT_VERSION, '2.5', '>=')) {
            $components['schematic_elementIndexSettings'] = [
                'class' => Service\ElementIndexSettings::class,
            ];
        }

        $this->setComponents($components);
    }

    /**
     * Install Craft.
     */
    private function _installCraft()
    {
        $options = [
            'username' => getenv('CRAFT_USERNAME'),
            'email' => getenv('CRAFT_EMAIL'),
            'password' => getenv('CRAFT_PASSWORD'),
            'siteName' => getenv('CRAFT_SITENAME'),
            'siteUrl' => getenv('CRAFT_SITEURL'),
            'locale' => getenv('CRAFT_LOCALE'),
        ];

        Craft::app()->install->run($options);
    }
}
