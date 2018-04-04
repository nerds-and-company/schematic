<?php

namespace NerdsAndCompany\Schematic\Interfaces;

// Declare the interface 'iTemplate'
interface MappingInterface
{
    public function export();
    public function import(array $definitions);
}
