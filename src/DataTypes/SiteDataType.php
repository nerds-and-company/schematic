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
        $this->clearSiteCaches();
        if (Schematic::$force) {
            $this->clearEmptyGroups();
        }
    }

    /**
     * Reset craft site service sites cache using reflection.
     */
    private function clearSiteCaches()
    {
        $obj = Craft::$app->sites;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_sitesById')) {
            $refProperty1 = $refObject->getProperty('_sitesById');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, null);
        }
        if ($refObject->hasProperty('_sitesByHandle')) {
            $refProperty2 = $refObject->getProperty('_sitesByHandle');
            $refProperty2->setAccessible(true);
            $refProperty2->setValue($obj, null);
        }
        $obj->init(); // reload sites
    }

    /**
     * Clear empty sute groups
     */
    private function clearEmptyGroups()
    {
        foreach (Craft::$app->sites->getAllGroups() as $group) {
            if (count($group->getSites()) == 0) {
                Craft::$app->sites->deleteGroup($group);
            }
        }
    }
}
