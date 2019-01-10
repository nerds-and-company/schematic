<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic GlobalSets DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSetDataType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getMapperHandle(): string
    {
        return 'modelMapper';
    }

    /**
     * Get data of this type.
     *
     * @return array
     */
    public function getRecords(): array
    {
        return Craft::$app->globals->getAllSets();
    }

    /**
     * Reset craft global sets cache using reflection.
     */
    public function afterImport()
    {
        $obj = Craft::$app->globals;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_allGlobalSets')) {
            $refProperty1 = $refObject->getProperty('_allGlobalSets');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, null);
        }
    }
}
