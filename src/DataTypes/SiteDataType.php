<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Sites DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SiteDataType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getMapperHandle(): string
    {
        return 'modelMapper';
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        return Craft::$app->sites->getAllSites();
    }

    /**
     * {@inheritdoc}
     */
    public function afterImport()
    {
        $this->resetCraftSiteServiceSiteIdsCache();
    }

    /**
     * Reset craft site service site ids cache using reflection.
     */
    private function resetCraftSiteServiceSiteIdsCache()
    {
        $obj = Craft::$app->sites;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_sitesById')) {
            $refProperty1 = $refObject->getProperty('_sitesById');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, null);
            $obj->init(); // reload sites
        }
    }
}
