<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;

abstract class Base implements DataTypeInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function getMapperHandle(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getRecords(): array;

    /**
     * {@inheritdoc}
     */
    public function afterImport()
    {
    }
}
