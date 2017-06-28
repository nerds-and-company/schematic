<?php

namespace NerdsAndCompany\Schematic\Console;

use Craft\Craft;
use Craft\ConsoleCommandRunner;
use Craft\StringHelper;
use Craft\IOHelper;

/**
 * Schematic Console Command Runner.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class CommandRunner extends ConsoleCommandRunner
{
    // Public Methods
    // =========================================================================

    /**
     * @param string $name command name (case-insensitive)
     *
     * @return \CConsoleCommand The command object. Null if the name is invalid.
     */
    public function createCommand($name)
    {
        $name = StringHelper::toLowerCase($name);

        $command = null;

        if (isset($this->commands[$name])) {
            $command = $this->commands[$name];
        } else {
            $commands = array_change_key_case($this->commands);

            if (isset($commands[$name])) {
                $command = $commands[$name];
            }
        }

        if ($command !== null) {
            if (is_string($command)) {
                $className = 'NerdsAndCompany\Schematic\ConsoleCommands\\'.IOHelper::getFileName($command, false);

                return new $className($name, $this);
            } else {
                // an array configuration

                return Craft::createComponent($command, $name, $this);
            }
        } elseif ($name === 'help') {
            return new \CHelpCommand('help', $this);
        } else {
            return;
        }
    }
}
