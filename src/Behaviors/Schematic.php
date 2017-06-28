<?php

namespace NerdsAndCompany\Schematic\Behaviors;

use Craft\Craft;
use Craft\AppBehavior as Base;

/**
 * Schematic Behavior.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Schematic extends Base
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
                if (Craft::app()->getComponent('db')) {
                    // If the db config isn't valid, then we'll assume it's not installed.
                    if (!Craft::app()->getIsDbConnectionValid()) {
                        return false;
                    }
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }

            $this->_isInstalled = Craft::app()->db->tableExists('info', false);
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
