<?php

namespace NerdsAndCompany\Schematic\Events;

use yii\base\Event;

/**
 * Schematic Converter Event.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ConverterEvent extends Event
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var string
     */
    public $converterClass;
}
