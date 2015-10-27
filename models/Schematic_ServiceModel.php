<?php

namespace Craft;

/**
 * Schematic Plugin container.
 *
 * Encapsulates data that has been exported via schematic.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_ServiceModel extends BaseModel
{
    /**
     * @var array
     */
    protected $services = array();


    /**
     * @param null $attributes
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);
        craft()->plugins->callFirst('registerMigrationService', array($this));
    }

    /**
     * @param string $handle
     * @param Schematic_AbstractService $service
     */
    public function addService($handle, Schematic_AbstractService $service)
    {
        $this->services[$handle] = $service;
    }

    /**
     * @param string $handle
     */
    public function removeService($handle)
    {
        if (array_key_exists($handle, $this->services)) {
            unset($this->services[$handle]);
        }
    }

    /**
     * @return Schematic_AbstractService[]
     */
    public function getServices()
    {
        return $this->services;
    }
}
