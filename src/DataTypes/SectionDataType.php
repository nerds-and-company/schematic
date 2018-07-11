<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Sections DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SectionDataType extends Base
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
        return Craft::$app->sections->getAllSections();
    }

    /**
     * Reset craft editable sections cache using reflection.
     */
    public function afterImport()
    {
        $obj = Craft::$app->sections;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_editableSectionIds')) {
            $refProperty1 = $refObject->getProperty('_editableSectionIds');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, $obj->getAllSectionIds());
        }
    }
}
