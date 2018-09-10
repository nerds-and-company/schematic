<?php

namespace NerdsAndCompany\Schematic\Events;

use yii\base\Event;

/**
 * Schematic Source Mapping Event.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SourceMappingEvent extends Event
{
    /**
     * @var string
     */
    public $source;

    /**
     * @var string
     */
    public $service;

    /**
     * @var string
     */
    public $method;
}
