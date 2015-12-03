<?php

namespace Craft;

/**
 * Schematic Behavior.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class SchematicBehavior extends AppBehavior
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    private $_isInstalled;

    /**
     * Determines if Craft is installed by checking if the info table exists.
     *
     * @return bool
     */
    public function isInstalled()
    {
        if (!isset($this->_isInstalled)) {
            try {
                // First check to see if DbConnection has even been initialized, yet.
                if (craft()->getComponent('db')) {
                    // If the db config isn't valid, then we'll assume it's not installed.
                    if (!craft()->getIsDbConnectionValid()) {
                        return false;
                    }
                } else {
                    return false;
                }
            } catch (DbConnectException $e) {
                return false;
            }

            $this->_isInstalled = craft()->db->tableExists('info', false);
        }

        return $this->_isInstalled;
    }

    /**
     * Tells Craft that it's installed now.
     */
    public function setIsInstalled()
    {
        // If you say so!
        $this->_isInstalled = true;
    }

    /**
     * Schematic requires the pro edition.
     *
     * @return string
     */
    public function getEdition()
    {
        return Craft::Pro;
    }
}
