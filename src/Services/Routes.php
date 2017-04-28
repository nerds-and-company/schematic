<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\RoutesService;

/**
 * Schematic Locales Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Routes extends Base
{
    /**
     * @return RoutesService
     */
    protected function getRoutesService()
    {
        return Craft::app()->routes;
    }

    /**
     * @param array $localeDefinitions
     * @param bool  $force
     *
     * @return \NerdsAndCompany\Schematic\Models\Result
     */
    public function import(array $localeDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Routes'));

        if ($force) {
            Craft::app()->db->createCommand()
                            ->from('routes')
                            ->delete();
        }

        foreach ($localeDefinitions as $route) {
            if (!$this->getRoutesService()->saveRoute(json_decode($route['urlParts']), $route['template'], null, $route['locale'])) {
                $this->addError(Craft::t('Route {route} could not be installed', ['route' => $route['urlParts']]));
            }
        }

        $order = array_map(function ($localeDefinition) {
            return $localeDefinition['id'];
        }, $localeDefinitions);

        // Re-order dbRoutes by definition
        $this->getRoutesService()->updateRouteOrder($order);

        return $this->getResultModel();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Routes'));

        return Craft::app()->db->createCommand()
                              ->select('urlParts, template, id, locale')
                              ->from('routes')
                              ->order('sortOrder')
                              ->queryAll();
    }
}
